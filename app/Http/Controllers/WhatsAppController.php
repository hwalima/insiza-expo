<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppBotService $bot) {}

    /** Wasender webhook — POST /api/webhook/whatsapp */
    public function webhook(Request $request): Response
    {
        // File-based debug log (works even if Laravel logging is broken)
        file_put_contents(
            storage_path('logs/wa-debug.log'),
            date('Y-m-d H:i:s') . " WEBHOOK HIT\n" .
            "Body: " . $request->getContent() . "\n" .
            "Headers: " . json_encode($request->headers->all()) . "\n\n",
            FILE_APPEND
        );

        Log::channel('stack')->info('WA webhook received', [
            'headers' => $request->headers->all(),
            'body'    => $request->getContent(),
        ]);

        // Webhook secret verification — temporarily log instead of blocking
        $secret = config('services.wasender.webhook_secret', '');
        if ($secret) {
            $incoming = $request->header('X-Secret')
                ?? $request->header('X-Wasender-Secret')
                ?? $request->header('X-Webhook-Secret')
                ?? $request->header('Authorization')
                ?? $request->input('secret')
                ?? '';
            if ($incoming !== $secret) {
                Log::warning('WA webhook: secret mismatch', [
                    'expected' => $secret,
                    'received' => $incoming,
                    'all_headers' => $request->headers->all(),
                ]);
                // Allow through for now — logging will reveal the correct header
            }
        }

        $payload = $request->json()->all();

        // Wasender sends different structures — extract from/text flexibly
        [$from, $body] = $this->extractMessage($payload);

        if (! $from || ! $body) {
            // Return 200 so Wasender doesn't retry; non-message events (receipts, etc.)
            return response('', 200);
        }

        // Sanitise number to E.164 digits only
        $from = preg_replace('/\D/', '', $from);

        // Don't reply to ourselves
        $botNumber = preg_replace('/\D/', '', config('services.wasender.bot_number', '263775536178'));
        if ($from === $botNumber) {
            Log::info('WA webhook: skipping message from bot itself');
            return response('', 200);
        }

        Log::info('WA incoming message', ['from' => $from, 'body' => substr($body, 0, 120)]);

        // Process AFTER response is sent — Wasender times out if we take too long
        $bot = $this->bot;
        $message = trim($body);
        app()->terminating(function () use ($bot, $from, $message) {
            try {
                $bot->handle($from, $message);
            } catch (\Throwable $e) {
                Log::error('WA bot error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        });

        return response('', 200);
    }

    /**
     * Extract [from, body] from any Wasender webhook structure.
     * Wasenderapi.com can send several formats; this handles them all.
     */
    private function extractMessage(array $p): array
    {
        // Format A: flat  {"from":"27...", "text":"Hello"}
        if (isset($p['from']) && (isset($p['text']) || isset($p['message']) || isset($p['body']))) {
            return [$p['from'], $p['text'] ?? $p['message'] ?? $p['body']];
        }

        // Format B: {"event":"message","data":{"from":"27...","body":"Hello"}}
        if (isset($p['data']['from'])) {
            $d = $p['data'];
            return [$d['from'], $d['body'] ?? $d['text'] ?? $d['message'] ?? ''];
        }

        // Format C: Wasender {"event":"messages.received","data":{"messages":{single object}}}
        if (isset($p['data']['messages']['key'])) {
            $msg  = $p['data']['messages'];
            $from = $msg['key']['cleanedSenderPn']
                ?? preg_replace('/@.*/', '', $msg['key']['senderPn'] ?? '')
                ?? $msg['key']['remoteJid']
                ?? '';
            $body = $msg['messageBody']
                ?? $msg['message']['conversation']
                ?? $msg['message']['extendedTextMessage']['text']
                ?? $msg['message']['imageMessage']['caption']
                ?? '';
            if (($msg['key']['fromMe'] ?? false) || empty($body)) {
                return ['', ''];
            }
            return [$from, $body];
        }

        // Format D: Baileys-style {"event":"messages.upsert","data":{"messages":[{...}]}}
        if (isset($p['data']['messages'][0])) {
            $msg  = $p['data']['messages'][0];
            $from = $msg['key']['cleanedSenderPn']
                ?? preg_replace('/@.*/', '', $msg['key']['senderPn'] ?? '')
                ?? $msg['key']['remoteJid']
                ?? '';
            $body = $msg['messageBody']
                ?? $msg['message']['conversation']
                ?? $msg['message']['extendedTextMessage']['text']
                ?? $msg['message']['imageMessage']['caption']
                ?? '';
            if (($msg['key']['fromMe'] ?? false) || empty($body)) {
                return ['', ''];
            }
            return [$from, $body];
        }

        // Format E: {"messages":[{"from":"27...", "text":"Hello"}]}
        if (isset($p['messages'][0])) {
            $m = $p['messages'][0];
            return [$m['from'] ?? '', $m['text'] ?? $m['body'] ?? ''];
        }

        return ['', ''];
    }
}
