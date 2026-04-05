<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class SubscriptionController extends Controller
{
    public static function price(): int
    {
        return (int) Setting::get('subscription_price', '3000');
    }

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    private static function isLocalhost(): bool
    {
        $url = config('app.url', '');
        return str_contains($url, 'localhost') || str_contains($url, '127.0.0.1');
    }

    private function buildPreferenceData(User $user, int $price): array
    {
        $appUrl    = rtrim(config('app.url'), '/');
        $isLocal   = static::isLocalhost();

        $data = [
            "items" => [[
                "id"          => "subscription-owner",
                "title"       => "Suscripción ReservasAR - Propietario",
                "description" => "Pago unico para recibir contactos, leer mensajes y gestionar reservas.",
                "category_id" => "services",
                "quantity"    => 1,
                "currency_id" => "ARS",
                "unit_price"  => (float) $price,
            ]],
            "payer" => [
                "name"  => $user->name,
                "email" => $user->email,
            ],
            "external_reference" => "subscription-{$user->id}",
        ];

        // MP requiere URLs públicas para back_urls y notification_url
        // En localhost se omiten para evitar errores de validación
        if (!$isLocal) {
            $data["back_urls"] = [
                "success" => $appUrl . '/usuario/suscripcion/exito',
                "failure" => $appUrl . '/usuario/suscripcion/fallo',
                "pending" => $appUrl . '/usuario/suscripcion/pendiente',
            ];
            $data["auto_return"]      = "approved";
            $data["notification_url"] = $appUrl . '/webhooks/mercadopago';
        }

        Log::info('[Subscription] Preference data built', [
            'is_local' => $isLocal,
            'app_url'  => $appUrl,
        ]);

        return $data;
    }

    // ── Crea preferencia en MP y guarda en BD. Retorna init_point o null si falla. ──
    private function createPreference(User $user): ?string
    {
        $price  = static::price();
        $client = new PreferenceClient();

        try {
            $preference = $client->create($this->buildPreferenceData($user, $price));

            $spRecord = SubscriptionPayment::create([
                'user_id'          => $user->id,
                'mp_preference_id' => $preference->id,
                'amount'           => $price,
                'status'           => 'initiated',
            ]);

            Log::info('[Subscription] Preference created', [
                'user_id'                 => $user->id,
                'mp_preference_id'        => $preference->id,
                'subscription_payment_id' => $spRecord->id,
            ]);

            return $preference->init_point;

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            Log::error('[Subscription] MP API error creating preference', [
                'user_id'       => $user->id,
                'error'         => $e->getMessage(),
                'status_code'   => $apiResponse?->getStatusCode(),
                'response_body' => $apiResponse?->getContent(),
            ]);
        } catch (\Exception $e) {
            Log::error('[Subscription] Failed to create MP preference', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return null;
    }

    // ── Redirige directo a MercadoPago (desde alertas) ────────────────────────
    public function redirectToMP()
    {
        $user = auth()->user();

        if ($user->hasSubscription()) {
            return redirect()->route('owner.properties.index')
                ->with('info', '¡Ya tenés tu suscripción activa!');
        }

        $initPoint = $this->createPreference($user);

        if (!$initPoint) {
            return redirect()->route('subscription.payment')
                ->with('error', 'No se pudo conectar con MercadoPago. Intentá de nuevo.');
        }

        return redirect()->away($initPoint);
    }

    // ── Muestra la página de pago ─────────────────────────────────────────────
    public function show()
    {
        $user = auth()->user();

        if ($user->hasSubscription()) {
            return redirect()->route('owner.properties.index')
                ->with('info', '¡Ya tenés tu suscripción activa!');
        }

        $initPoint = $this->createPreference($user);
        $price     = static::price();
        $mpError   = $initPoint ? null : 'No se pudo conectar con MercadoPago.';

        return view('subscription.pago', compact('price', 'initPoint', 'mpError'));
    }

    // ── Callback: pago aprobado (redirect de MP) ──────────────────────────────
    public function success(Request $request)
    {
        $user        = auth()->user();
        $paymentId   = $request->get('payment_id');
        $status      = $request->get('status');
        $preferenceId = $request->get('preference_id');

        Log::info('[Subscription] Success callback received', [
            'user_id'       => $user?->id,
            'payment_id'    => $paymentId,
            'status'        => $status,
            'preference_id' => $preferenceId,
            'query'         => $request->all(),
        ]);

        if ($status === 'approved' && $paymentId && $user) {
            // Actualizar registro en BD
            $spRecord = SubscriptionPayment::where('mp_preference_id', $preferenceId)
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            if ($spRecord) {
                $spRecord->update([
                    'mp_payment_id' => $paymentId,
                    'status'        => 'approved',
                    'paid_at'       => now(),
                ]);
                Log::info('[Subscription] DB record updated to approved (success callback)', [
                    'subscription_payment_id' => $spRecord->id,
                    'mp_payment_id'           => $paymentId,
                ]);
            } else {
                // Fallback: crear registro si no existe (ej: navegación directa)
                SubscriptionPayment::create([
                    'user_id'          => $user->id,
                    'mp_preference_id' => $preferenceId,
                    'mp_payment_id'    => $paymentId,
                    'amount'           => static::price(),
                    'status'           => 'approved',
                    'paid_at'          => now(),
                ]);
                Log::warning('[Subscription] No existing record found, created new approved record', [
                    'user_id'    => $user->id,
                    'payment_id' => $paymentId,
                ]);
            }

            static::activateSubscription($user->id);

            return redirect()->route('owner.properties.index')
                ->with('success', '🎉 ¡Suscripción activada! Ahora podés ver quién te contacta, leer mensajes y mostrar tu información a los clientes.');
        }

        Log::warning('[Subscription] Success callback but status not approved', [
            'user_id' => $user?->id,
            'status'  => $status,
        ]);

        return redirect()->route('owner.properties.index')
            ->with('info', 'Recibimos tu pago, estamos procesándolo. Te avisaremos cuando se confirme.');
    }

    // ── Callback: pago rechazado ───────────────────────────────────────────────
    public function failure(Request $request)
    {
        $user         = auth()->user();
        $preferenceId = $request->get('preference_id');
        $paymentId    = $request->get('payment_id');

        Log::warning('[Subscription] Failure callback received', [
            'user_id'       => $user?->id,
            'payment_id'    => $paymentId,
            'preference_id' => $preferenceId,
            'query'         => $request->all(),
        ]);

        if ($preferenceId && $user) {
            SubscriptionPayment::where('mp_preference_id', $preferenceId)
                ->where('user_id', $user->id)
                ->latest()
                ->update(['status' => 'rejected']);
        }

        return redirect()->route('subscription.payment')
            ->with('error', 'El pago fue rechazado. Podés intentar nuevamente.');
    }

    // ── Callback: pago pendiente ───────────────────────────────────────────────
    public function pending(Request $request)
    {
        $user         = auth()->user();
        $preferenceId = $request->get('preference_id');
        $paymentId    = $request->get('payment_id');

        Log::info('[Subscription] Pending callback received', [
            'user_id'       => $user?->id,
            'payment_id'    => $paymentId,
            'preference_id' => $preferenceId,
            'query'         => $request->all(),
        ]);

        if ($preferenceId && $user) {
            SubscriptionPayment::where('mp_preference_id', $preferenceId)
                ->where('user_id', $user->id)
                ->latest()
                ->update([
                    'mp_payment_id' => $paymentId,
                    'status'        => 'pending',
                ]);
        }

        return redirect()->route('owner.properties.index')
            ->with('info', 'Tu pago está pendiente de acreditación. Te avisaremos cuando se confirme.');
    }

    // ── Activar suscripción en users (llamado desde success y webhook) ─────────
    public static function activateSubscription(int $userId): void
    {
        User::withoutGlobalScope('active')->where('id', $userId)->update([
            'subscription_paid'    => true,
            'subscription_paid_at' => now(),
        ]);

        Log::info('[Subscription] User subscription activated', ['user_id' => $userId]);

        $user = User::find($userId);
        if ($user) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\SubscriptionActivatedNotification($user));
            } catch (\Throwable $e) {
                Log::error('[Subscription] Failed to send activation email', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    // ── Procesar pago vía webhook (llamado desde PaymentController) ───────────
    public static function processWebhookPayment(int $userId, string $mpPaymentId, object $payment): void
    {
        Log::info('[Subscription] Webhook processing payment', [
            'user_id'       => $userId,
            'mp_payment_id' => $mpPaymentId,
            'mp_status'     => $payment->status,
            'mp_status_detail' => $payment->status_detail ?? null,
            'preference_id' => $payment->preference_id ?? null,
        ]);

        // Buscar registro existente por preference_id o mp_payment_id
        $spRecord = SubscriptionPayment::where('mp_preference_id', $payment->preference_id ?? null)
            ->where('user_id', $userId)
            ->latest()
            ->first()
            ?? SubscriptionPayment::where('mp_payment_id', $mpPaymentId)->latest()->first();

        $mpResponse = [];
        try {
            // Convertir objeto MP a array para guardar en BD
            $mpResponse = json_decode(json_encode($payment), true) ?? [];
        } catch (\Exception $e) {
            Log::warning('[Subscription] Could not serialize MP response', ['error' => $e->getMessage()]);
        }

        $statusMap = [
            'approved'   => 'approved',
            'pending'    => 'pending',
            'in_process' => 'pending',
            'rejected'   => 'rejected',
            'cancelled'  => 'cancelled',
            'refunded'   => 'cancelled',
        ];
        $dbStatus = $statusMap[$payment->status] ?? 'pending';

        if ($spRecord) {
            $spRecord->update([
                'mp_payment_id'    => $mpPaymentId,
                'status'           => $dbStatus,
                'mp_status_detail' => $payment->status_detail ?? null,
                'payment_method'   => $payment->payment_method_id ?? null,
                'payment_type'     => $payment->payment_type_id ?? null,
                'mp_response'      => $mpResponse,
                'paid_at'          => $payment->status === 'approved' ? now() : null,
            ]);

            Log::info('[Subscription] DB record updated via webhook', [
                'subscription_payment_id' => $spRecord->id,
                'new_status'              => $dbStatus,
            ]);
        } else {
            // Sin registro previo (pago iniciado fuera del flujo normal)
            $spRecord = SubscriptionPayment::create([
                'user_id'          => $userId,
                'mp_preference_id' => $payment->preference_id ?? null,
                'mp_payment_id'    => $mpPaymentId,
                'amount'           => $payment->transaction_amount ?? static::price(),
                'status'           => $dbStatus,
                'mp_status_detail' => $payment->status_detail ?? null,
                'payment_method'   => $payment->payment_method_id ?? null,
                'payment_type'     => $payment->payment_type_id ?? null,
                'mp_response'      => $mpResponse,
                'paid_at'          => $payment->status === 'approved' ? now() : null,
            ]);

            Log::warning('[Subscription] No prior DB record found, created via webhook', [
                'subscription_payment_id' => $spRecord->id,
                'user_id'                 => $userId,
            ]);
        }

        if ($payment->status === 'approved') {
            static::activateSubscription($userId);
        }
    }
}
