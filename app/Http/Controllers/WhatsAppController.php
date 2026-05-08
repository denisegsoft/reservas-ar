<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBotService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function webhook(Request $request, WhatsAppBotService $bot)
    {
        $secret = $request->bearerToken();

        if ($secret !== config('services.whatsapp.webhook_secret')) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $data = $request->validate([
            'from'      => 'required|string',
            'jid'       => 'required|string',
            'message'   => 'required|string',
            'messageId' => 'required|string',
            'timestamp' => 'required|numeric',
        ]);

        // El buffer/debounce se maneja en el lado Node.js
        // Laravel recibe el mensaje ya consolidado y procesa directo
        $bot->handle($data['jid'], $data['from'], $data['message']);

        return response()->json(['received' => true]);
    }
}
