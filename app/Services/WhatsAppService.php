<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->post("{$this->apiUrl}/send", [
                'to'      => $to,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage());
            return false;
        }
    }
}
