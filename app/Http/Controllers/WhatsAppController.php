<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWhatsAppBuffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WhatsAppController extends Controller
{
    // Segundos de espera tras el último mensaje antes de responder
    const BUFFER_DELAY = 5;

    public function webhook(Request $request)
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

        $from = $data['from'];
        $jid  = $data['jid'];

        // Acumular el mensaje en el buffer del usuario
        $buffer   = Cache::get("wa_buffer_{$from}", []);
        $buffer[] = trim($data['message']);
        Cache::put("wa_buffer_{$from}", $buffer, now()->addMinutes(5));

        // Token único: si llega otro mensaje, este job queda obsoleto
        $token = uniqid('', true);
        Cache::put("wa_buffer_token_{$from}", $token, now()->addMinutes(5));

        // Procesar después de N segundos de silencio
        ProcessWhatsAppBuffer::dispatch($jid, $from, $token)
            ->delay(now()->addSeconds(self::BUFFER_DELAY));

        return response()->json(['received' => true]);
    }
}
