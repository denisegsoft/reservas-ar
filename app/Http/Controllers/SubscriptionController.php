<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\MailHelper;
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

    private function isPublicUrl(): bool
    {
        $url = config('app.url', '');
        return !str_contains($url, 'localhost') && !str_contains($url, '127.0.0.1');
    }

    private function buildPreferenceData(User $user, int $price): array
    {
        $appUrl = rtrim(config('app.url'), '/');

        $data = [
            "items" => [[
                "id"          => "subscription-owner",
                "title"       => "Suscripción " . config('app.name') . " - Propietario",
                "description" => "Pago único para recibir contactos, leer mensajes y gestionar reservas.",
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

        if ($this->isPublicUrl()) {
            $data["back_urls"] = [
                "success" => $appUrl . '/usuario/suscripcion/exito',
                "failure" => $appUrl . '/usuario/suscripcion/fallo',
                "pending" => $appUrl . '/usuario/suscripcion/pendiente',
            ];
            $data["auto_return"]      = "approved";
            $data["notification_url"] = $appUrl . '/webhooks/mercadopago';
        }

        return $data;
    }

    private function createPreference(User $user): ?string
    {
        $price  = static::price();
        $client = new PreferenceClient();

        try {
            $preference = $client->create($this->buildPreferenceData($user, $price));

            SubscriptionPayment::create([
                'user_id'          => $user->id,
                'mp_preference_id' => $preference->id,
                'amount'           => $price,
                'status'           => 'initiated',
            ]);

            return $preference->init_point;

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            Log::error('[Subscription] MP API error creating preference', [
                'user_id'       => $user->id,
                'error'         => $e->getMessage(),
                'status_code'   => $e->getApiResponse()?->getStatusCode(),
                'response_body' => $e->getApiResponse()?->getContent(),
            ]);
        } catch (\Exception $e) {
            Log::error('[Subscription] Failed to create MP preference', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return null;
    }

    // Muestra la página de pago
    public function show()
    {
        $user = auth()->user();

        if ($user->hasSubscription()) {
            return redirect()->route('owner.dashboard')
                ->with('info', '¡Ya tenés tu suscripción activa!');
        }

        $initPoint = $this->createPreference($user);
        $price     = static::price();
        $mpError   = $initPoint ? null : 'No se pudo conectar con MercadoPago.';

        return view('subscription.pago', compact('price', 'initPoint', 'mpError'));
    }

    // Redirige directo a MercadoPago (desde alertas/banners)
    public function redirectToMP()
    {
        $user = auth()->user();

        if ($user->hasSubscription()) {
            return redirect()->route('owner.dashboard')
                ->with('info', '¡Ya tenés tu suscripción activa!');
        }

        $initPoint = $this->createPreference($user);

        if (!$initPoint) {
            return redirect()->route('subscription.payment')
                ->with('error', 'No se pudo conectar con MercadoPago. Intentá de nuevo.');
        }

        return redirect()->away($initPoint);
    }

    // Callback: pago aprobado
    public function success(Request $request)
    {
        $user         = auth()->user();
        $paymentId    = $request->get('payment_id');
        $status       = $request->get('status');
        $preferenceId = $request->get('preference_id');

        Log::info('[Subscription] Success callback', [
            'user_id'       => $user?->id,
            'payment_id'    => $paymentId,
            'status'        => $status,
            'preference_id' => $preferenceId,
        ]);

        if ($status === 'approved' && $paymentId && $user) {
            $spRecord = SubscriptionPayment::where('mp_preference_id', $preferenceId)
                ->where('user_id', $user->id)
                ->latest()->first();

            if ($spRecord) {
                $spRecord->update([
                    'mp_payment_id' => $paymentId,
                    'status'        => 'approved',
                    'paid_at'       => now(),
                ]);
            } else {
                SubscriptionPayment::create([
                    'user_id'          => $user->id,
                    'mp_preference_id' => $preferenceId,
                    'mp_payment_id'    => $paymentId,
                    'amount'           => static::price(),
                    'status'           => 'approved',
                    'paid_at'          => now(),
                ]);
            }

            static::activateSubscription($user->id);

            return redirect()->route('owner.dashboard')
                ->with('success', '¡Suscripción activada! Ahora podés ver quién te contacta, leer mensajes y gestionar tus reservas.');
        }

        return redirect()->route('owner.dashboard')
            ->with('info', 'Recibimos tu pago, estamos procesándolo. Te avisaremos cuando se confirme.');
    }

    // Callback: pago rechazado
    public function failure(Request $request)
    {
        $user         = auth()->user();
        $preferenceId = $request->get('preference_id');

        if ($preferenceId && $user) {
            SubscriptionPayment::where('mp_preference_id', $preferenceId)
                ->where('user_id', $user->id)
                ->latest()
                ->update(['status' => 'rejected']);
        }

        return redirect()->route('subscription.payment')
            ->with('error', 'El pago fue rechazado. Podés intentar nuevamente.');
    }

    // Callback: pago pendiente
    public function pending(Request $request)
    {
        $user         = auth()->user();
        $preferenceId = $request->get('preference_id');
        $paymentId    = $request->get('payment_id');

        if ($preferenceId && $user) {
            SubscriptionPayment::where('mp_preference_id', $preferenceId)
                ->where('user_id', $user->id)
                ->latest()
                ->update([
                    'mp_payment_id' => $paymentId,
                    'status'        => 'pending',
                ]);
        }

        return redirect()->route('owner.dashboard')
            ->with('info', 'Tu pago está pendiente de acreditación. Te avisaremos cuando se confirme.');
    }

    // Activar suscripción (llamado desde success y webhook)
    public static function activateSubscription(int $userId): void
    {
        User::withoutGlobalScope('active')->where('id', $userId)->update([
            'subscription_paid'    => true,
            'subscription_paid_at' => now(),
        ]);

        Log::info('[Subscription] Activated', ['user_id' => $userId]);

        $user = User::find($userId);
        if ($user) {
            MailHelper::send(
                $user->email,
                new \App\Mail\SubscriptionActivatedNotification($user),
                '[Subscription]',
                ['user_id' => $userId]
            );
        }
    }

    // Procesar pago vía webhook
    public static function processWebhookPayment(int $userId, string $mpPaymentId, object $payment): void
    {
        Log::info('[Subscription] Webhook payment', [
            'user_id'       => $userId,
            'mp_payment_id' => $mpPaymentId,
            'mp_status'     => $payment->status,
        ]);

        $spRecord = SubscriptionPayment::where('mp_preference_id', $payment->preference_id ?? null)
            ->where('user_id', $userId)
            ->latest()->first()
            ?? SubscriptionPayment::where('mp_payment_id', $mpPaymentId)->latest()->first();

        $statusMap = [
            'approved'   => 'approved',
            'pending'    => 'pending',
            'in_process' => 'pending',
            'rejected'   => 'rejected',
            'cancelled'  => 'cancelled',
            'refunded'   => 'cancelled',
        ];
        $dbStatus = $statusMap[$payment->status] ?? 'pending';

        $fields = [
            'mp_payment_id'    => $mpPaymentId,
            'status'           => $dbStatus,
            'mp_status_detail' => $payment->status_detail ?? null,
            'payment_method'   => $payment->payment_method_id ?? null,
            'payment_type'     => $payment->payment_type_id ?? null,
            'paid_at'          => $payment->status === 'approved' ? now() : null,
        ];

        if ($spRecord) {
            $spRecord->update($fields);
        } else {
            SubscriptionPayment::create(array_merge($fields, [
                'user_id'          => $userId,
                'mp_preference_id' => $payment->preference_id ?? null,
                'amount'           => $payment->transaction_amount ?? static::price(),
            ]));
        }

        if ($payment->status === 'approved') {
            static::activateSubscription($userId);
        }
    }
}
