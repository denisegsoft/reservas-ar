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

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function handle(string $from, string $message): void
    {
        $message = trim($message);
        $estado  = Cache::get("wa_estado_{$from}");

        if ($estado === 'esperando_federacion') {
            $this->handleSeleccionFederacion($from, $message);
            return;
        }

        $federacion = $this->detectarFederacion($message);

        if ($federacion) {
            $this->saludar($from, $federacion);
        } else {
            $this->pedirFederacion($from);
        }
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

    private function handleSeleccionFederacion(string $from, string $mensaje): void
    {
        $texto      = strtoupper(trim($mensaje));
        $federacion = self::OPCIONES[$mensaje] ?? self::FEDERACIONES[$texto] ?? null;

        if ($federacion) {
            Cache::forget("wa_estado_{$from}");
            $this->saludar($from, $federacion);
        } else {
            $this->whatsapp->send($from,
                "No reconocí esa opción. Por favor respondé con el número o nombre de tu federación:\n\n" .
                "1. FDPATIN\n2. FCBM\n3. FECHIDA\n4. FUN"
            );
        }
    }

    private function saludar(string $from, string $federacion): void
    {
        $this->whatsapp->send($from,
            "¡Hola! Te saluda Candela de {$federacion}. ¿En qué puedo ayudarte hoy?"
        );
    }

    private function pedirFederacion(string $from): void
    {
        Cache::put("wa_estado_{$from}", 'esperando_federacion', now()->addHours(2));

        $this->whatsapp->send($from,
            "¡Hola! ¿A cuál de nuestras federaciones pertenecés?\n\n" .
            "1. FDPATIN\n2. FCBM\n3. FECHIDA\n4. FUN"
        );
    }
}
