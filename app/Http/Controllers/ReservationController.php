<?php

namespace App\Http\Controllers;

use App\Mail\NewReservationNotification;
use App\Mail\ReservationCancelledOwnerNotification;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationService;
use App\Services\PricingService;
use App\Support\MailHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function __construct(private readonly PricingService $pricing) {}

    public function create(Property $propiedad)
    {
        return redirect()->route('properties.show', $propiedad->slug);
    }

    public function store(Request $request, Property $propiedad)
    {
        if (!Auth::check()) {
            session([
                'pending_reservation'      => $request->except('_token'),
                'pending_reservation_slug' => $propiedad->slug,
            ]);
            return redirect()->route('login');
        }

        $request->validate([
            'check_in'       => 'required|date|after_or_equal:today',
            'check_in_time'  => 'nullable|string|max:5',
            'check_out'      => 'required|date|after:check_in',
            'check_out_time' => 'nullable|string|max:5',
            'guests'         => 'required|integer|min:1',
            'notes'          => 'nullable|string|max:500',
        ], [
            'check_in.required'       => 'La fecha de entrada es requerida.',
            'check_in.date'           => 'La fecha de entrada no es válida.',
            'check_in.after_or_equal' => 'La fecha de entrada debe ser hoy o posterior.',
            'check_out.required'      => 'La fecha de salida es requerida.',
            'check_out.date'          => 'La fecha de salida no es válida.',
            'check_out.after'         => 'La fecha de salida debe ser posterior a la de entrada.',
            'guests.required'         => 'La cantidad de personas es requerida.',
            'guests.integer'          => 'La cantidad de personas debe ser un número entero.',
            'guests.min'              => 'La cantidad de personas debe ser al menos 1.',
        ]);

        $result = $this->buildReservation($request->only([
            'check_in', 'check_in_time', 'check_out', 'check_out_time', 'guests', 'notes',
        ]), $propiedad);

        if (isset($result['error'])) {
            return back()->withErrors($result['error']);
        }

        return redirect()->route('reservations.show', $result['reservation'])
            ->with('reservation_created', true);
    }

    public function buildReservation(array $data, Property $propiedad): array
    {
        if (!$propiedad->isAvailable($data['check_in'] ?? '', $data['check_out'] ?? '')) {
            return ['error' => ['check_in' => 'Las fechas seleccionadas no están disponibles.']];
        }

        $calc = $this->pricing->calculate($data, $propiedad);
        if (isset($calc['error'])) {
            return $calc;
        }

        ['breakdown' => $breakdown, 'subtotal' => $subtotal, 'totalDays' => $totalDays, 'pricePerDay' => $pricePerDay] = $calc;

        $reservation = Reservation::create([
            'property_id'     => $propiedad->id,
            'user_id'         => Auth::id(),
            'check_in'        => $data['check_in'],
            'check_in_time'   => $data['check_in_time'] ?? null,
            'check_out'       => $data['check_out'],
            'check_out_time'  => $data['check_out_time'] ?? null,
            'guests'          => $data['guests'],
            'price_per_day'   => $pricePerDay,
            'total_days'      => $totalDays,
            'subtotal'        => $subtotal,
            'price_breakdown' => $breakdown,
            'service_fee'     => 0,
            'total_amount'    => $subtotal,
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'notes'           => $data['notes'] ?? null,
        ]);

        MailHelper::send(
            $propiedad->owner->email,
            new NewReservationNotification($propiedad->owner, $propiedad),
            '[Reservation]',
            ['reservation_id' => $reservation->id]
        );

        return ['reservation' => $reservation];
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['property.images', 'property.owner', 'property.services', 'payment', 'services.propertyService']);
        $this->pricing->recalculate($reservation);
        return view('reservations.show', compact('reservation'));
    }

    public function payment(Reservation $reservation)
    {
        abort_if($reservation->isPaid(), 404);
        $reservation->load(['property', 'payment']);
        return view('reservations.payment', compact('reservation'));
    }

    public function cancel(Reservation $reservation)
    {
        abort_if($reservation->isCancelled() || $reservation->isConfirmed() && $reservation->check_in->isPast(), 403);

        $reservation->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Cancelada por el cliente',
        ]);

        $reservation->load(['user', 'property.owner']);
        MailHelper::send(
            $reservation->property->owner->email,
            new ReservationCancelledOwnerNotification($reservation),
            '[Reservation]',
            ['reservation_id' => $reservation->id]
        );

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reserva cancelada correctamente.');
    }

    public function updateServices(Reservation $reservation, Request $request)
    {
        abort_if($reservation->isCancelled(), 403);

        $reservation->services()->delete();
        foreach ($request->input('reservation_services', []) as $s) {
            if (empty($s['property_service_id'])) continue;
            ReservationService::create([
                'reservation_id'      => $reservation->id,
                'property_service_id' => $s['property_service_id'],
                'quantity'            => (float) ($s['quantity'] ?? 1),
                'price'               => (float) ($s['price'] ?? 0),
            ]);
        }

        return back()->with('success', 'Servicios actualizados correctamente.');
    }

    public function myReservations()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->with(['property.images', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reservations.my-reservations', compact('reservations'));
    }

    // ── Backward-compatible static wrappers ───────────────────────────────────
    // These delegate to PricingService so existing calls from DashboardController
    // continue to work without changes.

    /** @deprecated Inject PricingService directly instead. */
    public static function calculateBreakdown(array $data, Property $propiedad): array
    {
        return app(PricingService::class)->calculate($data, $propiedad);
    }

    /** @deprecated Inject PricingService directly instead. */
    public static function ensureBreakdown(Reservation $reservation): void
    {
        app(PricingService::class)->recalculate($reservation);
    }
}
