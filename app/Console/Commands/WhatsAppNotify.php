<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Expo;
use App\Services\WhatsAppBotService;
use Illuminate\Console\Command;

class WhatsAppNotify extends Command
{
    protected $signature = 'whatsapp:notify
        {--message= : Custom message to send}
        {--status=approved : Filter by booking status (approved|pending|all)}
        {--paid-only : Only notify exhibitors with verified payment}';

    protected $description = 'Send a WhatsApp notification to all exhibitors with active bookings';

    public function handle(): int
    {
        $expo = Expo::active();
        if (! $expo) {
            $this->error('No active expo found.');
            return 1;
        }

        $message = $this->option('message');
        if (! $message) {
            $message = $this->ask('Enter the message to send to exhibitors');
        }
        if (! $message) {
            $this->error('Message is required.');
            return 1;
        }

        $status = $this->option('status');
        $paidOnly = $this->option('paid-only');

        $query = Booking::with(['stand', 'user'])
            ->where('expo_id', $expo->id);

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($paidOnly) {
            $query->where('payment_verified', true);
        }

        $bookings = $query->get();

        if ($bookings->isEmpty()) {
            $this->warn('No matching bookings found.');
            return 0;
        }

        $this->info("Found {$bookings->count()} booking(s). Sending notifications...");

        if (! $this->confirm("Send to {$bookings->count()} exhibitor(s)?")) {
            $this->info('Cancelled.');
            return 0;
        }

        $bot = app(WhatsAppBotService::class);
        $sent = 0;

        foreach ($bookings as $booking) {
            $personalised = str_replace(
                ['{name}', '{company}', '{stand}', '{expo}', '{date}'],
                [
                    $booking->contact_person,
                    $booking->company_name,
                    $booking->stand?->stand_number ?? 'N/A',
                    $expo->name,
                    $expo->start_date->format('d M Y'),
                ],
                $message
            );

            $bot->sendExpoReminder($booking, $personalised);
            $sent++;
            $this->line("  ✓ {$booking->company_name} ({$booking->contact_phone})");
        }

        $this->info("Done! Sent {$sent} notification(s).");
        return 0;
    }
}
