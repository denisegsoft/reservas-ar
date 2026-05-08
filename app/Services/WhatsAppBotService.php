<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WhatsAppBotService
{
    const FEDERACIONES = [
        'FDPATIN' => 'FDPATIN',
        'FCBM'    => 'FCBM',
        'FECHIDA' => 'FECHIDA',
        'FUN'     => 'FUN',
    ];

    // Opciones numéricas para cuando el usuario elige
    const OPCIONES = [
        '1' => 'FDPATIN',
        '2' => 'FCBM',
        '3' => 'FECHIDA',
        '4' => 'FUN',
    ];

    private WhatsAppService $whatsapp;
    private ClaudeService   $claude;

    public function __construct(WhatsAppService $whatsapp, ClaudeService $claude)
    {
        $this->whatsapp = $whatsapp;
        $this->claude   = $claude;
    }

    public function handle(string $jid, string $from, string $message): void
    {
        $message = trim($message);

        // 1. Buscar primero el nombre de plataforma en cualquier parte del mensaje
        $federacionEnMensaje = $this->detectarFederacion($message);

        if ($federacionEnMensaje) {
            $federacionAnterior = Cache::get("wa_federacion_{$from}");

            // Si cambió de federación, limpiar historial para empezar de cero
            if ($federacionAnterior && $federacionAnterior !== $federacionEnMensaje) {
                $this->claude->limpiarHistorial($from);
            }

            Cache::put("wa_federacion_{$from}", $federacionEnMensaje, now()->addDays(30));
            Cache::forget("wa_estado_{$from}");

            $respuesta = $this->claude->chat($from, $federacionEnMensaje, $message);
            $this->whatsapp->send($jid, $respuesta);
            return;
        }

        // 2. Verificar si ya conocemos la federación de este número
        $federacionGuardada = Cache::get("wa_federacion_{$from}");

        if ($federacionGuardada) {
            $respuesta = $this->claude->chat($from, $federacionGuardada, $message);
            $this->whatsapp->send($jid, $respuesta);
            return;
        }

        // 3. Si estamos esperando que elija del menú
        $estado = Cache::get("wa_estado_{$from}");

        if ($estado === 'esperando_federacion') {
            $this->handleSeleccionFederacion($jid, $from, $message);
            return;
        }

        // 4. No sabemos nada: pedir la federación
        $this->pedirFederacion($jid, $from);
    }

    private function detectarFederacion(string $texto): ?string
    {
        $texto = strtoupper($texto);

        foreach (self::FEDERACIONES as $key => $nombre) {
            if (str_contains($texto, $key)) {
                return $nombre;
            }
        }

        return null;
    }

    private function handleSeleccionFederacion(string $jid, string $from, string $mensaje): void
    {
        $texto      = strtoupper(trim($mensaje));
        $federacion = self::OPCIONES[$mensaje] ?? self::FEDERACIONES[$texto] ?? null;

        if ($federacion) {
            Cache::put("wa_federacion_{$from}", $federacion, now()->addDays(30));
            Cache::forget("wa_estado_{$from}");

            // Primer contacto: Claude saluda al usuario como Candela
            $respuesta = $this->claude->chat($from, $federacion, 'Hola');
            $this->whatsapp->send($jid, $respuesta);
        } else {
            $this->whatsapp->send($jid,
                "No reconocí esa opción. Por favor respondé con el número o nombre de tu federación:\n\n"
                . "1. FDPATIN\n2. FCBM\n3. FECHIDA\n4. FUN"
            );
        }
    }

    private function pedirFederacion(string $jid, string $from): void
    {
        Cache::put("wa_estado_{$from}", 'esperando_federacion', now()->addHours(2));

        $this->whatsapp->send($jid,
            "¡Hola! ¿A cuál de nuestras federaciones pertenecés?\n\n"
            . "1. FDPATIN\n2. FCBM\n3. FECHIDA\n4. FUN"
        );
    }
}
