<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wasender API gateway — sends WhatsApp messages via wasender.app
 */
class WasenderService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.wasenderapi.com/api/send-message';

    public function __construct()
    {
        $this->apiKey = config('services.wasender.api_key', '');
    }

    public function sendText(string $to, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('Wasender: API key not configured.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl, [
                'to'   => $this->normaliseNumber($to),
                'text' => $message,
            ]);

            if (! $response->successful()) {
                Log::error('Wasender send failed', ['status' => $response->status(), 'body' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Wasender exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /** Ensure E.164 format without leading + for Wasender */
    private function normaliseNumber(string $number): string
    {
        $clean = preg_replace('/\D/', '', $number);
        // Prefix Zimbabwe country code if local number
        if (strlen($clean) === 10 && str_starts_with($clean, '0')) {
            $clean = '263' . substr($clean, 1);
        }
        return $clean;
    }
}
