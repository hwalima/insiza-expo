<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\StandStatus;
use App\Mail\BookingConfirmation;
use App\Models\Attendee;
use App\Models\Booking;
use App\Models\Expo;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class WhatsAppBotService
{
    private const TTL = 3600;

    public function __construct(
        private readonly WasenderService $wasender,
        private readonly GrokService     $grok,
    ) {}

    public function handle(string $from, string $body): void
    {
        $user  = $this->resolveUser($from);
        $state = $this->getState($from);
        $clean = trim($body);

        if (preg_match('/\b(IDI\d{4}-\d{4})\b/i', strtoupper($clean), $m)) {
            $code     = strtoupper($m[1]);
            $attendee = Attendee::where('registration_number', $code)->first();
            if ($attendee) {
                $status = $attendee->checked_in
                    ? "✅ Checked in – " . $attendee->checked_in_at?->format('d M Y H:i')
                    : "⏳ Not yet checked in";
                $this->reply($from,
                    "🎟️ IDIEXPO Registration Verified\n" .
                    "Name: {$attendee->name}\n" .
                    ($attendee->organisation ? "Organisation: {$attendee->organisation}\n" : "") .
                    "Reg No: {$attendee->registration_number}\n" .
                    "Status: {$status}\n\n" .
                    "Verify link: " . $attendee->verifyUrl()
                );
                return;
            }

            $this->reply($from, "Sorry, registration number {$code} was not found. Please check and try again.");
            return;
        }

        $expo = Expo::active();
        if (! $expo) {
            $this->reply($from, "Hi! There is no active expo at the moment. Visit https://insizaexpo.co.zw for updates.");
            return;
        }

        if ($this->isCancelCommand($clean)) {
            unset($state['booking']);
            $this->setState($from, $state);
            $this->reply($from, "Booking cancelled. Send MENU to see options.");
            return;
        }

        if ($this->isMenuCommand($clean)) {
            unset($state['booking']);
            $this->setState($from, $state);
            $this->reply($from, $this->menuText($expo));
            return;
        }

        if ($this->isListCommand($clean)) {
            $reply = $this->listStands($expo, null, $this->isAllCommand($clean));
            $this->setState($from, $state);
            $this->reply($from, $reply);
            return;
        }

        if ($this->isMyBookingsCommand($clean)) {
            $reply = $this->myBookings($user);
            $this->setState($from, $state);
            $this->reply($from, $reply);
            return;
        }

        if (isset($state['booking'])) {
            $reply = $this->handleBookingDraft($clean, $from, $user, $expo, $state);
            $this->setState($from, $state);
            $this->reply($from, $reply);
            return;
        }

        if ($this->isBookingIntentCommand($clean)) {
            $state['booking'] = ['stage' => 'stand'];
            $this->setState($from, $state);
            $this->reply($from, $this->askBookingQuestion($state['booking'], $expo));
            return;
        }

        // AI-powered general conversation
        $systemPrompt = $this->buildSystemPrompt($expo);
        $history      = $state['history'] ?? [];
        $reply        = $this->grok->chat($systemPrompt, $history, $body);

        $intent = $this->extractIntent($reply);

        if ($intent) {
            $actionReply = $this->executeIntent($intent, $from, $user, $expo, $state);
            if ($actionReply) {
                $reply = $actionReply;
            }
        }

        $history[] = ['role' => 'user',      'content' => $body];
        $history[] = ['role' => 'assistant',  'content' => $reply];
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $this->setState($from, array_merge($state, ['history' => $history]));
        $this->reply($from, $reply);
    }

    // ─── Step-by-step booking draft handler ──────────────────────────────

    private function handleBookingDraft(string $input, string $from, ?User $user, Expo $expo, array &$state): string
    {
        $draft = $state['booking'];
        $stage = $draft['stage'] ?? 'stand';

        switch ($stage) {
            case 'stand':
                $standNumber = $this->extractStandNumber($input);
                if (! $standNumber) {
                    return "Please enter a valid stand number (e.g. A1, B3). Reply LIST to see available stands, or CANCEL to stop.";
                }
                $stand = $this->resolveStand($standNumber, $expo);
                if (! $stand) {
                    return "Stand {$standNumber} is not available. Reply LIST to see available stands, or try another stand number.";
                }
                $draft['stand_number'] = $standNumber;
                $draft['stand_id'] = $stand->id;
                $draft['stage'] = 'company';
                break;

            case 'company':
                if (strlen($input) < 2) {
                    return "Please enter your company or organisation name.";
                }
                $draft['company_name'] = $input;
                $draft['stage'] = 'contact_person';
                break;

            case 'contact_person':
                if (strlen($input) < 2) {
                    return "Please enter the full name of the contact person.";
                }
                $draft['contact_person'] = $input;
                $draft['stage'] = 'email';
                break;

            case 'email':
                if (! $this->validateEmail($input)) {
                    return "That doesn't look like a valid email. Please enter a valid email address.";
                }
                $draft['email'] = strtolower(trim($input));
                $draft['stage'] = 'phone';
                break;

            case 'phone':
                $phone = $this->extractPhone($input);
                if (! $phone) {
                    return "Please enter a valid phone number (at least 9 digits).";
                }
                $draft['phone'] = $phone;
                $draft['stage'] = 'category';
                break;

            case 'category':
                $category = $this->normalizeCategory($input);
                if (! $category) {
                    return "Please choose a category: mining, agriculture, education, organisations, or general. Reply SKIP to use general.";
                }
                $draft['category'] = $category;
                $draft['stage'] = 'confirm';
                break;

            case 'confirm':
                if ($this->isConfirmation($input)) {
                    $result = $this->createBooking($draft, $from, $user, $expo, $state);
                    return $result;
                }
                if ($this->isDenial($input)) {
                    unset($state['booking']);
                    return "Booking cancelled. Send BOOK to start again or MENU for options.";
                }
                return "Please reply YES to confirm or NO to cancel.\n\n" . $this->formatDraftSummary($draft, $expo);
        }

        $state['booking'] = $draft;

        if ($stage !== 'confirm' && $draft['stage'] === 'confirm') {
            return $this->formatDraftSummary($draft, $expo) . "\n\nReply YES to confirm or NO to cancel.";
        }

        return $this->askBookingQuestion($draft, $expo);
    }

    private function askBookingQuestion(array $draft, Expo $expo): string
    {
        return match ($draft['stage'] ?? 'stand') {
            'stand'          => "📍 Which stand would you like to book?\n\nReply with a stand number (e.g. A1, B3), or LIST to see available stands.\n\nReply CANCEL at any time to stop.",
            'company'        => "🏢 What is your company/organisation name?",
            'contact_person' => "👤 Who is the contact person for this booking?",
            'email'          => "📧 What email address should we use for this booking?",
            'phone'          => "📱 What phone number should we reach you on?",
            'category'       => "📋 What category best fits your business?\n\n• Mining\n• Agriculture\n• Education\n• Organisations\n• General\n\nReply with one, or SKIP for general.",
            'confirm'        => $this->formatDraftSummary($draft, $expo) . "\n\nReply YES to confirm or NO to cancel.",
            default          => "Send MENU to see options.",
        };
    }

    private function formatDraftSummary(array $draft, Expo $expo): string
    {
        $stand = Stand::find($draft['stand_id'] ?? 0);
        $price = $stand ? "USD {$stand->price}" : 'TBC';
        $size  = $stand?->size?->label() ?? 'N/A';

        return "📋 *Booking Summary*\n" .
            "━━━━━━━━━━━━━━━━━━━━\n" .
            "Expo: {$expo->name}\n" .
            "Stand: {$draft['stand_number']} ({$size})\n" .
            "Price: {$price}\n" .
            "Company: {$draft['company_name']}\n" .
            "Contact: {$draft['contact_person']}\n" .
            "Email: {$draft['email']}\n" .
            "Phone: {$draft['phone']}\n" .
            "Category: " . ucfirst($draft['category'] ?? 'general');
    }

    private function createBooking(array $draft, string $from, ?User $user, Expo $expo, array &$state): string
    {
        $stand = $this->resolveStand($draft['stand_number'], $expo);
        if (! $stand) {
            unset($state['booking']);
            return "Sorry, stand {$draft['stand_number']} was just taken. Reply LIST to see what's still available.";
        }

        if (! $user) {
            $user = $this->createOrUpdateWhatsappUser($draft, $from);
        } else {
            $user->fill([
                'name'        => $draft['contact_person'] ?: $user->name,
                'email'       => $draft['email'] ?: $user->email,
                'phone'       => $draft['phone'] ?: $user->phone,
                'whatsapp_id' => $from,
            ]);
            $user->save();
            if (! $user->hasRole('exhibitor')) {
                $user->assignRole('exhibitor');
            }
        }

        $booking = Booking::create([
            'stand_id'       => $stand->id,
            'user_id'        => $user->id,
            'expo_id'        => $expo->id,
            'company_name'   => $draft['company_name'],
            'contact_person' => $draft['contact_person'],
            'contact_email'  => $draft['email'],
            'contact_phone'  => $draft['phone'],
            'category'       => $draft['category'] ?? 'general',
            'status'         => BookingStatus::Pending,
            'source'         => 'whatsapp',
        ]);

        $stand->update(['status' => StandStatus::Reserved]);
        unset($state['booking']);

        dispatch(function () use ($booking) {
            $booking->load(['stand', 'expo']);
            if ($booking->contact_email) {
                $mailer = new NotificationMailer();
                $mailer->send(new BookingConfirmation($booking), $booking->contact_email);
            }
        })->afterResponse();

        $paymentInfo = $this->getPaymentInstructions($stand, $expo);

        return "✅ *Booking Confirmed!*\n" .
            "━━━━━━━━━━━━━━━━━━━━\n" .
            "Stand {$stand->stand_number} has been reserved for {$draft['company_name']}.\n" .
            "Booking Ref: #BK{$booking->id}\n\n" .
            "💳 *Payment*\n" .
            "━━━━━━━━━━━━━━━━━━━━\n" .
            "{$paymentInfo}\n\n" .
            "Once your payment is confirmed at the office, you will be notified here on WhatsApp.\n\n" .
            "Send MY BOOKINGS to check your booking status anytime.";
    }

    private function getPaymentInstructions(Stand $stand, Expo $expo): string
    {
        return "Amount: USD {$stand->price}\n\n" .
            "Payment is done in person at the Insiza RDC offices.\n" .
            "Reference: IDIEXPO-{$stand->stand_number}\n\n" .
            "📍 Visit the office to complete your payment.\n" .
            "For queries, WhatsApp: +263775536178\n" .
            "Or call: {$expo->contact_phone}";
    }

    // ─── Notification methods (called from admin actions) ────────────────

    public function notifyPaymentReceived(Booking $booking): void
    {
        $user = $booking->user;
        $to = $user?->whatsapp_id ?? $user?->phone ?? $booking->contact_phone;
        if (! $to) return;

        $message = "💰 *Payment Confirmed!*\n" .
            "━━━━━━━━━━━━━━━━━━━━\n" .
            "Hi {$booking->contact_person},\n\n" .
            "We have received and verified your payment for Stand {$booking->stand?->stand_number}.\n" .
            "Booking Ref: #BK{$booking->id}\n" .
            "Company: {$booking->company_name}\n\n" .
            "Your booking is now confirmed. We will send you event updates and reminders as the expo date approaches.\n\n" .
            "Send MY BOOKINGS to see all your bookings.";

        $this->reply($to, $message);
    }

    public function notifyBookingApproved(Booking $booking): void
    {
        $user = $booking->user;
        $to = $user?->whatsapp_id ?? $user?->phone ?? $booking->contact_phone;
        if (! $to) return;

        $booking->load(['stand', 'expo']);

        $message = "🎉 *Booking Approved!*\n" .
            "━━━━━━━━━━━━━━━━━━━━\n" .
            "Hi {$booking->contact_person},\n\n" .
            "Great news! Your booking for Stand {$booking->stand?->stand_number} at {$booking->expo?->name} has been approved.\n\n" .
            "📍 Stand: {$booking->stand?->stand_number} ({$booking->stand?->size?->label()})\n" .
            "📅 Expo: {$booking->expo?->start_date?->format('d M')}–{$booking->expo?->end_date?->format('d M Y')}\n" .
            "📌 Venue: {$booking->expo?->venue}\n\n" .
            ($booking->payment_verified
                ? "✅ Payment: Verified\n\n"
                : "⚠️ Payment: Pending — please complete payment to secure your stand.\n\n") .
            "We'll send you setup details and reminders closer to the event date.\n\n" .
            "Send MY BOOKINGS anytime to check your status.";

        $this->reply($to, $message);
    }

    public function notifyBookingRejected(Booking $booking): void
    {
        $user = $booking->user;
        $to = $user?->whatsapp_id ?? $user?->phone ?? $booking->contact_phone;
        if (! $to) return;

        $message = "❌ *Booking Update*\n" .
            "━━━━━━━━━━━━━━━━━━━━\n" .
            "Hi {$booking->contact_person},\n\n" .
            "Unfortunately, your booking for Stand {$booking->stand?->stand_number} could not be approved at this time.\n\n" .
            ($booking->admin_notes ? "Reason: {$booking->admin_notes}\n\n" : "") .
            "If you believe this is an error, please contact us or reply here for assistance.\n\n" .
            "Reply BOOK to try booking a different stand, or LIST to see available stands.";

        $this->reply($to, $message);
    }

    public function sendExpoReminder(Booking $booking, string $message): void
    {
        $user = $booking->user;
        $to = $user?->whatsapp_id ?? $user?->phone ?? $booking->contact_phone;
        if (! $to) return;

        $this->reply($to, $message);
    }

    // ─── AI system prompt & intent extraction ────────────────────────────

    private function buildSystemPrompt(Expo $expo): string
    {
        $stands = Stand::where('expo_id', $expo->id)
            ->where('status', StandStatus::Available)
            ->get()
            ->map(fn($s) => "{$s->stand_number} ({$s->size->label()}, {$s->category->label()}, \${$s->price})")
            ->join(', ');

        return <<<PROMPT
You are the official AI assistant for the {$expo->name} at {$expo->venue}, {$expo->start_date->format('d M')}–{$expo->end_date->format('d M Y')}.

Available stands: {$stands}

Your job:
1. Answer questions about the expo.
2. Help exhibitors find and book stands.
3. When a user wants to book a stand, tell them to reply BOOK to start the booking process.
4. For listing stands, output: {"intent":"list_stands","category":"mining"} (category optional)
5. Keep replies concise and friendly. Use plain text (no markdown).
6. If someone asks about their bookings, tell them to reply MY BOOKINGS.

Key commands the user can use:
- MENU — see all options
- BOOK — start booking a stand
- LIST — see available stands
- MY BOOKINGS — check booking status
- CANCEL — cancel current booking
PROMPT;
    }

    private function extractIntent(string $text): ?array
    {
        if (preg_match('/\{[^{}]+\"intent\"[^{}]+\}/s', $text, $m)) {
            $data = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return null;
    }

    private function executeIntent(array $intent, string $from, ?User $user, Expo $expo, array &$state): ?string
    {
        return match ($intent['intent'] ?? '') {
            'list_stands'  => $this->listStands($expo, $intent['category'] ?? null, ! empty($intent['all'] ?? false)),
            'my_bookings'  => $this->myBookings($user),
            default        => null,
        };
    }

    // ─── Stand listing & booking queries ─────────────────────────────────

    private function listStands(Expo $expo, ?string $category, bool $showAll = false): string
    {
        $q = Stand::where('expo_id', $expo->id);
        if (! $showAll) {
            $q->where('status', StandStatus::Available);
        }
        if ($category) {
            $q->where('category', $category);
        }

        $stands = $q->get();
        if ($stands->isEmpty()) {
            return "Sorry, no stands" . ($category ? " in {$category}" : "") . " match that filter right now.";
        }

        $lines = $stands->map(fn($s) =>
            "• {$s->stand_number}: {$s->size->label()}, {$s->category->label()}, USD {$s->price}" .
            ($showAll ? " — {$s->status->label()}" : "")
        )->join("\n");

        return ($showAll ? "📊 All stands:\n" : "📍 Available stands:\n") . $lines .
            "\n\n💡 Reply BOOK to start a booking, or send a stand number.";
    }

    private function myBookings(?User $user): string
    {
        if (! $user) {
            return "I don't recognise your number yet. Please book a stand first, or contact us directly.";
        }

        $bookings = $user->bookings()->with('stand')->latest()->get();
        if ($bookings->isEmpty()) {
            return "You don't have any bookings yet. Reply BOOK to start a stand booking.";
        }

        $lines = $bookings->map(function ($booking) {
            $statusIcon = match ($booking->status) {
                BookingStatus::Pending   => '⏳',
                BookingStatus::Approved  => '✅',
                BookingStatus::Rejected  => '❌',
                BookingStatus::Cancelled => '🚫',
            };
            $payment = $booking->payment_verified ? '💰 Paid' : '💳 Payment pending';
            return "• Stand {$booking->stand?->stand_number} ({$booking->stand?->size?->label()})\n" .
                "  {$statusIcon} {$booking->status->label()} | {$payment}\n" .
                "  Ref: #BK{$booking->id}";
        })->join("\n\n");

        return "📋 *Your Bookings*\n━━━━━━━━━━━━━━━━━━━━\n{$lines}\n\n💡 Reply BOOK to create another booking.";
    }

    // ─── Menu ────────────────────────────────────────────────────────────

    private function menuText(Expo $expo): string
    {
        return "🏛️ *{$expo->name}*\n" .
            "📅 {$expo->start_date->format('d M')}–{$expo->end_date->format('d M Y')}\n" .
            "📌 {$expo->venue}\n" .
            "━━━━━━━━━━━━━━━━━━━━\n\n" .
            "What would you like to do?\n\n" .
            "1️⃣ *BOOK* — Book a stand\n" .
            "2️⃣ *LIST* — See available stands\n" .
            "3️⃣ *ALL STANDS* — See all stands (incl. taken)\n" .
            "4️⃣ *MY BOOKINGS* — Check your bookings\n\n" .
            "Just type any command or ask me a question about the expo!";
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function resolveStand(string $standNumber, Expo $expo): ?Stand
    {
        return Stand::where('expo_id', $expo->id)
            ->where('stand_number', strtoupper($standNumber))
            ->where('status', StandStatus::Available)
            ->first();
    }

    private function extractStandNumber(string $text): ?string
    {
        if (preg_match('/\b([A-Z]\d{1,3})\b/i', $text, $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    private function validateEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    private function extractPhone(string $text): ?string
    {
        $digits = preg_replace('/\D/', '', $text);
        if (strlen($digits) < 9 || strlen($digits) > 15) {
            return null;
        }
        return $digits;
    }

    private function normalizeCategory(string $value): ?string
    {
        $value = strtolower(trim($value));
        $aliases = [
            'mining'        => 'mining',
            'agriculture'   => 'agriculture',
            'agric'         => 'agriculture',
            'farming'       => 'agriculture',
            'education'     => 'education',
            'edu'           => 'education',
            'school'        => 'education',
            'organisations' => 'organisations',
            'organisation'  => 'organisations',
            'org'           => 'organisations',
            'ngo'           => 'organisations',
            'general'       => 'general',
            'other'         => 'general',
            'skip'          => 'general',
            '1'             => 'mining',
            '2'             => 'agriculture',
            '3'             => 'education',
            '4'             => 'organisations',
            '5'             => 'general',
        ];

        return $aliases[$value] ?? null;
    }

    private function isConfirmation(string $input): bool
    {
        return preg_match('/^\s*(yes|y|confirm|yebo|sure|ok|proceed)\s*$/i', $input) === 1;
    }

    private function isDenial(string $input): bool
    {
        return preg_match('/^\s*(no|n|cancel|nope|stop)\s*$/i', $input) === 1;
    }

    private function createOrUpdateWhatsappUser(array $draft, string $from): User
    {
        $user = User::where('whatsapp_id', $from)
            ->orWhere('phone', $draft['phone'] ?? $from)
            ->first();

        if (! $user) {
            $user = User::create([
                'name'        => $draft['contact_person'] ?? 'WhatsApp User',
                'email'       => $draft['email'] ?? ($from . '@wa.insizaexpo.co.zw'),
                'password'    => Hash::make(str()->random(16)),
                'phone'       => $draft['phone'] ?? $from,
                'whatsapp_id' => $from,
            ]);
        } else {
            $user->fill([
                'name'        => $draft['contact_person'] ?: $user->name,
                'email'       => $draft['email'] ?: $user->email,
                'phone'       => $draft['phone'] ?: $user->phone,
                'whatsapp_id' => $from,
            ]);
            $user->save();
        }

        if (! $user->hasRole('exhibitor')) {
            $user->assignRole('exhibitor');
        }

        return $user;
    }

    private function isListCommand(string $message): bool
    {
        return preg_match('/^\s*(list|available|show\s*stands?|see\s*stands?|available\s*stands?|2)\s*$/i', $message) === 1;
    }

    private function isAllCommand(string $message): bool
    {
        return preg_match('/^\s*(all|all\s*stands?|every\s*stand|3)\s*$/i', $message) === 1;
    }

    private function isMyBookingsCommand(string $message): bool
    {
        return preg_match('/^\s*(my\s*bookings?|booking\s*status|check\s*my\s*booking|status|4)\s*$/i', $message) === 1;
    }

    private function isBookingIntentCommand(string $message): bool
    {
        return preg_match('/^\s*(book|reserve|booking|book\s*a?\s*stand|reserve\s*a?\s*stand|1)\s*$/i', $message) === 1;
    }

    private function isMenuCommand(string $message): bool
    {
        return preg_match('/^\s*(menu|help|hi|hello|start|hey|options)\s*$/i', $message) === 1;
    }

    private function isCancelCommand(string $message): bool
    {
        return preg_match('/^\s*(cancel|stop|quit|exit)\s*$/i', $message) === 1;
    }

    private function resolveUser(string $from): ?User
    {
        return User::where('whatsapp_id', $from)->orWhere('phone', $from)->first();
    }

    private function getState(string $from): array
    {
        return Cache::get("wa_state:{$from}", []);
    }

    private function setState(string $from, array $state): void
    {
        Cache::put("wa_state:{$from}", $state, self::TTL);
    }

    private function reply(string $to, string $message): void
    {
        $this->wasender->sendText($to, $message);
    }
}
