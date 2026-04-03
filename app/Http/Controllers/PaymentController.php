<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

class PaymentController extends Controller
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function createPreference(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        abort_if($reservation->isPaid(), 400, 'Ya fue pagada.');

        $client = new PreferenceClient();

        $preferenceData = [
            "items" => [
                [
                    "id" => "property-{$reservation->property_id}",
                    "title" => "Reserva: " . $reservation->property->name,
                    "description" => "Check-in: {$reservation->check_in->format('d/m/Y')} - Check-out: {$reservation->check_out->format('d/m/Y')} ({$reservation->total_days} noches)",
                    "category_id" => "services",
                    "quantity" => 1,
                    "currency_id" => "ARS",
                    "unit_price" => (float) $reservation->total_amount,
                ],
            ],
            "payer" => [
                "name" => $reservation->user->name,
                "email" => $reservation->user->email,
            ],
            "back_urls" => [
                "success" => route('payments.success', $reservation),
                "failure" => route('payments.failure', $reservation),
                "pending" => route('payments.pending', $reservation),
            ],
            "auto_return" => "approved",
            "external_reference" => "reservation-{$reservation->id}",
            "notification_url" => route('payments.webhook'),
            "statement_descriptor" => "ReservaQuintas",
            "expires" => true,
            "expiration_date_from" => now()->toIso8601String(),
            "expiration_date_to" => now()->addHours(24)->toIso8601String(),
        ];

        $preference = $client->create($preferenceData);

        // Store preference
        Payment::updateOrCreate(
            ['reservation_id' => $reservation->id],
            ['mp_preference_id' => $preference->id, 'amount' => $reservation->total_amount, 'status' => 'pending']
        );

        $sandboxUrl = "https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id={$preference->id}";

        $isSandbox = str_starts_with(config('services.mercadopago.access_token', ''), 'TEST-');
        $initPoint = $isSandbox
            ? ($preference->sandbox_init_point ?? $preference->init_point)
            : $preference->init_point;

        return response()->json([
            'preference_id' => $preference->id,
            'init_point'    => $initPoint,
        ]);
    }

    public function success(Request $request, Reservation $reservation)
    {
        $paymentId = $request->get('payment_id');
        $status = $request->get('status');

        if ($status === 'approved' && $paymentId) {
            $this->processPayment($reservation, $paymentId, 'approved');
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', '¡Pago realizado con éxito! Tu reserva está confirmada.');
    }

    public function failure(Request $request, Reservation $reservation)
    {
        return redirect()->route('reservations.payment', $reservation)
            ->with('error', 'El pago fue rechazado. Podés intentar nuevamente.');
    }

    public function pending(Request $request, Reservation $reservation)
    {
        return redirect()->route('reservations.show', $reservation)
            ->with('info', 'Tu pago está pendiente de acreditación. Te notificaremos cuando se confirme.');
    }

    public function webhook(Request $request)
    {
        $topic = $request->get('topic') ?? $request->get('type');
        $id = $request->get('id') ?? $request->get('data.id');

        Log::info('MercadoPago Webhook', $request->all());

        if ($topic === 'payment' && $id) {
            try {
                $client = new PaymentClient();
                $payment = $client->get($id);

                if ($payment && $payment->external_reference) {
                    $ref = $payment->external_reference;

                    if (str_starts_with($ref, 'subscription-')) {
                        // Pago de suscripción de propietario
                        $userId = (int) str_replace('subscription-', '', $ref);
                        if ($userId) {
                            \App\Http\Controllers\SubscriptionController::processWebhookPayment($userId, (string) $id, $payment);
                        }
                    } elseif (str_starts_with($ref, 'reservation-')) {
                        // Pago de reserva
                        $reservationId = str_replace('reservation-', '', $ref);
                        $reservation = Reservation::find($reservationId);
                        if ($reservation) {
                            $this->processPayment($reservation, $id, $payment->status);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Webhook payment error: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function processPayment(Reservation $reservation, string $paymentId, string $status): void
    {
        $paymentRecord = Payment::firstOrNew(['reservation_id' => $reservation->id]);
        $paymentRecord->mp_payment_id = $paymentId;
        $paymentRecord->status = $status;
        $paymentRecord->amount = $reservation->total_amount;

        if ($status === 'approved') {
            $paymentRecord->paid_at = now();
            $paymentRecord->save();

            $reservation->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);
        } else {
            $paymentRecord->save();
        }
    }
}
