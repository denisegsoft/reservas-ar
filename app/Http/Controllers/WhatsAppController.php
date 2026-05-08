<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsAppBotService;

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

        $bot->handle($data['jid'], $data['from'], $data['message']);

        return response()->json(['received' => true]);
    }
}
