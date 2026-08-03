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
use App\Services\NotificationMailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Orchestrates the WhatsApp bot conversation flow:
 *  incoming message → Grok NLU → DB lookup/action → reply via Wasender
 */
class WhatsAppBotService
{
    // Conversation states stored in cache keyed by whatsapp_id
    private const TTL = 3600; // 1 hour session

    public function __construct(
        private readonly WasenderService $wasender,
        private readonly GrokService     $grok,
    ) {}

    public function handle(string $from, string $body): void
    {
        $user  = $this->resolveUser($from);
        $state = $this->getState($from);

        // Quick registration number lookup (IDIxxxx-NNNN pattern, no AI needed)
        if (preg_match('/\b(IDI\d{4}-\d{4})\b/i', strtoupper(trim($body)), $m)) {
            $code     = strtoupper($m[1]);
            $attendee = Attendee::where('registration_number', $code)->first();
            if ($attendee) {
                $status = $attendee->checked_in
                    ? "✅ *Checked In* – " . $attendee->checked_in_at?->format('d M Y H:i')
                    : "⏳ *Not yet checked in*";
                $this->reply($from,
                    "🎟️ *IDIEXPO Registration Verified*\n" .
                    "Name: {$attendee->name}\n" .
                    ($attendee->organisation ? "Organisation: {$attendee->organisation}\n" : "") .
                    "Reg No: {$attendee->registration_number}\n" .
                    "Status: {$status}\n\n" .
                    "Verify link: " . $attendee->verifyUrl()
                );
                return;
            } else {
                $this->reply($from, "❌ Registration number *{$code}* not found. Please check and try again.");
                return;
            }
        }

        $expo  = Expo::active();
        if (! $expo) {
            $this->reply($from, "Hi! There is no active expo at the moment. Visit https://insizaexpo.co.zw for updates.");
            return;
        }

        $systemPrompt = $this->buildSystemPrompt($expo);
        $history      = $state['history'] ?? [];
        $reply        = $this->grok->chat($systemPrompt, $history, $body);

        // Parse structured intent from Grok's reply (JSON block detection)
        $intent = $this->extractIntent($reply);

        if ($intent) {
            $actionReply = $this->executeIntent($intent, $from, $user, $expo, $state);
            if ($actionReply) {
                $reply = $actionReply;
            }
        }

        // Append to history (keep last 10 turns)
        $history[] = ['role' => 'user',      'content' => $body];
        $history[] = ['role' => 'assistant',  'content' => $reply];
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $this->setState($from, array_merge($state, ['history' => $history]));
        $this->reply($from, $reply);
    }

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
3. When a user wants to book a stand, collect: company name, contact person, email, phone, category, stand number.
4. Once you have all info, output a JSON block like: {"intent":"book","stand_number":"A1","company_name":"ABC","contact_person":"John","email":"j@co.zw","phone":"+263771234567","category":"mining"}
5. For listing stands, output: {"intent":"list_stands","category":"mining"} (category optional)
6. Keep replies concise and friendly. Use plain text (no markdown).
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
        return match($intent['intent'] ?? '') {
            'list_stands' => $this->listStands($expo, $intent['category'] ?? null),
            'book'        => $this->bookStand($intent, $from, $user, $expo),
            default       => null,
        };
    }

    private function listStands(Expo $expo, ?string $category): string
    {
        $q = Stand::where('expo_id', $expo->id)->where('status', StandStatus::Available);
        if ($category) {
            $q->where('category', $category);
        }
        $stands = $q->get();

        if ($stands->isEmpty()) {
            return "Sorry, no available stands" . ($category ? " in the {$category} category" : "") . " at the moment.";
        }

        $lines = $stands->map(fn($s) => "• {$s->stand_number}: {$s->size->label()}, {$s->category->label()}, USD {$s->price}")->join("\n");
        return "Available stands:\n{$lines}\n\nReply with the stand number you'd like to book.";
    }

    private function bookStand(array $intent, string $from, ?User $user, Expo $expo): string
    {
        $stand = Stand::where('expo_id', $expo->id)
            ->where('stand_number', $intent['stand_number'] ?? '')
            ->where('status', StandStatus::Available)
            ->first();

        if (! $stand) {
            return "Sorry, stand {$intent['stand_number']} is not available. Reply LIST to see available stands.";
        }

        // Auto-create a guest user if none exists
        if (! $user) {
            $user = User::create([
                'name'         => $intent['contact_person'] ?? 'WhatsApp User',
                'email'        => $intent['email'] ?? ($from . '@wa.insizaexpo.co.zw'),
                'password'     => Hash::make(str()->random(16)),
                'phone'        => $intent['phone'] ?? $from,
                'whatsapp_id'  => $from,
            ]);
            $user->assignRole('exhibitor');
        }

        Booking::create([
            'stand_id'       => $stand->id,
            'user_id'        => $user->id,
            'expo_id'        => $expo->id,
            'company_name'   => $intent['company_name']   ?? 'Unknown',
            'contact_person' => $intent['contact_person'] ?? $user->name,
            'contact_email'  => $intent['email']          ?? $user->email,
            'contact_phone'  => $intent['phone']          ?? $from,
            'category'       => $intent['category']       ?? 'general',
            'status'         => BookingStatus::Pending,
            'source'         => 'whatsapp',
        ]);

        $stand->update(['status' => StandStatus::Reserved]);

        // Send booking confirmation email
        dispatch(function () use ($booking) {
            $booking->load(['stand', 'expo']);
            $mailer = new NotificationMailer();
            if ($booking->contact_email) {
                $mailer->send(new BookingConfirmation($booking), $booking->contact_email);
            }
        })->afterResponse();

        return "Your booking request for Stand {$stand->stand_number} ({$stand->size->label()}) has been submitted! Our team will review and confirm within 24 hours. Thank you!";
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
