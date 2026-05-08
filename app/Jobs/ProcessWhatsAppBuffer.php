<?php

namespace App\Jobs;

use App\Services\WhatsAppBotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ProcessWhatsAppBuffer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $jid,
        private string $from,
        private string $token,
    ) {}

    public function handle(WhatsAppBotService $bot): void
    {
        // Si llegó un mensaje más reciente, este job queda obsoleto
        if (Cache::get("wa_buffer_token_{$this->from}") !== $this->token) {
            return;
        }

        $buffer = Cache::pull("wa_buffer_{$this->from}", []);
        Cache::forget("wa_buffer_token_{$this->from}");

        if (empty($buffer)) {
            return;
        }

        // Unir todos los mensajes en uno solo (como si los hubiera escrito juntos)
        $mensajeCompleto = implode("\n", $buffer);

        $bot->handle($this->jid, $this->from, $mensajeCompleto);
    }
}
