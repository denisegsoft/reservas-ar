<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClaudeService
{
    const MAX_MENSAJES   = 20;   // últimos 10 turnos (user+assistant)
    const CACHE_TTL_HORAS = 24;

    public function chat(string $from, string $federacion, string $mensaje): string
    {
        $historial = Cache::get("wa_historial_{$from}", []);

        $historial[] = ['role' => 'user', 'content' => $mensaje];

        $systemPrompt = $this->buildSystemPrompt($federacion);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.claude.api_key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('services.claude.model', 'claude-haiku-4-5'),
                'max_tokens' => 1024,
                'system'     => $systemPrompt,
                'messages'   => $historial,
            ]);

            if ($response->successful()) {
                $texto = $response->json('content.0.text')
                    ?? 'Lo siento, no pude procesar tu consulta.';

                $historial[] = ['role' => 'assistant', 'content' => $texto];

                // Mantener solo los últimos N mensajes para no superar el contexto
                if (count($historial) > self::MAX_MENSAJES) {
                    $historial = array_slice($historial, -self::MAX_MENSAJES);
                }

                Cache::put(
                    "wa_historial_{$from}",
                    $historial,
                    now()->addHours(self::CACHE_TTL_HORAS)
                );

                return $texto;
            }

            $errorBody = $response->json() ?? $response->body();
            Log::error('Claude API error', [
                'status' => $response->status(),
                'body'   => $errorBody,
            ]);

            throw new \RuntimeException(
                'Claude HTTP ' . $response->status() . ': ' . json_encode($errorBody)
            );

        } catch (\RuntimeException $e) {
            throw $e; // dejar subir para que el controller lo capture

        } catch (\Exception $e) {
            Log::error('ClaudeService exception', ['error' => $e->getMessage()]);

            throw new \RuntimeException('Claude connection error: ' . $e->getMessage());
        }
    }

    public function limpiarHistorial(string $from): void
    {
        Cache::forget("wa_historial_{$from}");
    }

    private function buildSystemPrompt(string $federacion): string
    {
        $base = "Sos Candela, la asistente virtual de {$federacion}. "
            . "Respondés siempre en español argentino, de forma amable, clara y profesional. "
            . "Tus respuestas son concisas porque estás en WhatsApp (máximo 3-4 párrafos cortos). "
            . "Tu objetivo es ayudar a los usuarios con consultas sobre la federación: "
            . "inscripciones, eventos, licencias, requisitos y cualquier información relevante. "
            . "Si no sabés algo, lo decís honestamente y sugerís que contacten a la sede.";

        $knowledgePath = "knowledge/{$federacion}.txt";

        if (Storage::exists($knowledgePath)) {
            $knowledge = Storage::get($knowledgePath);
            $base .= "\n\nInformación de {$federacion} que debés usar para responder:\n\n{$knowledge}";
        }

        return $base;
    }
}
