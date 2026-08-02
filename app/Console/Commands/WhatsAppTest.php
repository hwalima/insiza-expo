<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WhatsAppTest extends Command
{
    protected $signature   = 'whatsapp:test {--to= : Recipient number} {--msg= : Message text}';
    protected $description = 'Send a test WhatsApp message via Wasender';

    public function handle(): int
    {
        $key = config('services.wasender.api_key');
        $to  = preg_replace('/\D/', '', $this->option('to') ?? env('WASENDER_TEST_NUMBER', '27785425978'));
        $msg = $this->option('msg') ?? "Hello from IDIEXPO 2026! This is a test message from the Insiza District Industrial Expo app. The AI assistant and booking system are live.";

        if (empty($key)) { $this->error('WASENDER_API_KEY not set'); return 1; }
        $this->info("Sending to: +{$to}");

        $r = Http::withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ])->post('https://api.wasenderapi.com/api/send-message', [
            'to'   => $to,
            'text' => $msg,
        ]);

        $this->line("Status: {$r->status()}");
        $this->line("Body:   {$r->body()}");

        if ($r->successful()) { $this->info('Message sent successfully!'); return 0; }
        $this->error('Send failed.'); return 1;
    }
}