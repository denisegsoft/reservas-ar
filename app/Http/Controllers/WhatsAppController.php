<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function webhook(Request $request)
    {
        // Verificar el secret que manda Node
        $secret = $request->bearerToken();

        if ($secret !== config('services.whatsapp.webhook_secret')) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $data = $request->validate([
            'from'      => 'required|string',
            'message'   => 'required|string',
            'messageId' => 'required|string',
            'timestamp' => 'required|numeric',
        ]);

        // Por ahora devolvemos los datos recibidos para verificar que llegan bien
        return response()->json([
            'received' => true,
            'data'     => [
                'from'      => $data['from'],
                'message'   => $data['message'],
                'messageId' => $data['messageId'],
                'timestamp' => $data['timestamp'],
                'datetime'  => date('Y-m-d H:i:s', $data['timestamp']),
            ],
        ]);
    }
}
