<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppBotService $bot) {}

    /** Wasender webhook — POST /webhook/whatsapp */
    public function webhook(Request $request): Response
    {
        // Verify webhook secret
        $secret = config('services.wasender.webhook_secret', '');
        if ($secret && $request->header('X-Wasender-Secret') !== $secret) {
            return response('Unauthorized', 401);
        }

        $payload = $request->json();
        $from    = $payload->get('from') ?? $payload->get('sender');
        $body    = $payload->get('message') ?? $payload->get('text') ?? '';

        if (! $from || ! $body) {
            return response('', 200);
        }

        Log::info('WhatsApp incoming', ['from' => $from, 'body' => substr($body, 0, 120)]);

        // Dispatch async so Wasender gets 200 quickly
        dispatch(fn() => $this->bot->handle($from, trim($body)))->afterResponse();

        return response('', 200);
    }
}
