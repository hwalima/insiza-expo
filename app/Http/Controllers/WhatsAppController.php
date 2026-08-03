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
        // Always log the full raw payload for debugging
        Log::channel('stack')->info('WA webhook received', [
            'headers' => $request->headers->all(),
            'body'    => $request->getContent(),
        ]);

        // Optional webhook secret verification
        $secret = config('services.wasender.webhook_secret', '');
        if ($secret && $request->header('X-Wasender-Secret') !== $secret) {
            Log::warning('WA webhook: invalid secret');
            return response('Unauthorized', 401);
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

        Log::info('WA incoming message', ['from' => $from, 'body' => substr($body, 0, 120)]);

        // Fire-and-forget after response so Wasender gets 200 immediately
        dispatch(fn() => $this->bot->handle($from, trim($body)))->afterResponse();

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

        // Format C: Baileys-style {"event":"messages.upsert","data":{"messages":[{...}]}}
        if (isset($p['data']['messages'][0])) {
            $msg  = $p['data']['messages'][0];
            $from = $msg['key']['remoteJid'] ?? '';
            $body = $msg['message']['conversation']
                ?? $msg['message']['extendedTextMessage']['text']
                ?? $msg['message']['imageMessage']['caption']
                ?? '';
            // Skip messages sent BY us
            if (($msg['key']['fromMe'] ?? false) || empty($body)) {
                return ['', ''];
            }
            return [$from, $body];
        }

        // Format D: {"messages":[{"from":"27...", "text":"Hello"}]}
        if (isset($p['messages'][0])) {
            $m = $p['messages'][0];
            return [$m['from'] ?? '', $m['text'] ?? $m['body'] ?? ''];
        }

        return ['', ''];
    }
}
