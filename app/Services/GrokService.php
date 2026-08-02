<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Groq API — ultra-fast LLM inference for the AI assistant and WhatsApp bot
 */
class GrokService
{
    private string $apiKey;
    private string $model   = 'llama-3.3-70b-versatile'; // Groq's current recommended fast model
    private string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
    }

    public function chat(string $systemPrompt, array $history, string $userMessage): string
    {
        if (empty($this->apiKey)) {
            Log::warning('Grok: API key not configured.');
            return 'Sorry, the AI assistant is currently unavailable. Please call us directly.';
        }

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $turn) {
            $messages[] = $turn;
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl, [
                'model'      => $this->model,
                'messages'   => $messages,
                'max_tokens' => 512,
            ]);

            if (! $response->successful()) {
                Log::error('Grok API error', ['status' => $response->status(), 'body' => $response->body()]);
                return 'I encountered an error. Please try again.';
            }

            return $response->json('choices.0.message.content', 'No response.');
        } catch (\Throwable $e) {
            Log::error('Grok exception', ['error' => $e->getMessage()]);
            return 'I encountered an error. Please try again.';
        }
    }
}
