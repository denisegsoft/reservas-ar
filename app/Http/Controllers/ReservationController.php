<?php

namespace App\Http\Controllers;

use App\Mail\NewReservationNotification;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ReservationController extends Controller
{
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
            'notes'          => 'nullable|string|max:500',
        ], [
            'check_in.required'       => 'La fecha de entrada es requerida.',
            'check_in.date'           => 'La fecha de entrada no es válida.',
            'check_in.after_or_equal' => 'La fecha de entrada debe ser hoy o posterior.',
            'check_out.required'      => 'La fecha de salida es requerida.',
            'check_out.date'          => 'La fecha de salida no es válida.',
            'check_out.after'         => 'La fecha de salida debe ser posterior a la de entrada.',
        ]);

        $result = $this->buildReservation($request->only([
            'check_in', 'check_in_time', 'check_out', 'check_out_time', 'notes',
        ]), $propiedad);

        if (isset($result['error'])) {
            return back()->withErrors($result['error']);
        }

        return redirect()->route('reservations.show', $result['reservation'])
            ->with('reservation_created', true);
    }

public function buildReservation(array $data, Property $propiedad): array
    {
        $checkIn     = $data['check_in'] ?? null;
        $checkOut    = $data['check_out'] ?? null;
        $checkInTime = $data['check_in_time'] ?? null;
        $checkOutTime= $data['check_out_time'] ?? null;
        $notes       = $data['notes'] ?? null;

        $checkInDt  = Carbon::parse($checkIn  . ' ' . ($checkInTime  ?? '14:00'));
        $checkOutDt = Carbon::parse($checkOut . ' ' . ($checkOutTime ?? '11:00'));
        $totalHours = (int) $checkInDt->diffInHours($checkOutDt);
        $totalDays  = (int) $checkInDt->diffInDays($checkOutDt) ?: 1;

        if ($totalHours <= 0) {
            return ['error' => ['check_out' => 'La fecha y hora de salida debe ser posterior a la de entrada.']];
        }

        if ($totalDays < $propiedad->min_days) {
            return ['error' => ['check_out' => "La estadía mínima es de {$propiedad->min_days} día(s)."]];
        }

        if (!$propiedad->isAvailable($checkIn, $checkOut)) {
            return ['error' => ['check_in' => 'Las fechas seleccionadas no están disponibles.']];
        }

        $pricePerDay   = $propiedad->price_per_day;
        $pricePerHour  = $propiedad->price_per_hour;
        $pricePerWeek  = $propiedad->price_per_week;
        $pricePerMonth = $propiedad->price_per_month;

        if ($totalHours < 24) {
            $subtotal = round($pricePerHour * $totalHours, 2);
        } elseif ($totalDays < 7) {
            $subtotal = round($pricePerDay * $totalDays, 2);
        } elseif ($totalDays < 30) {
            $totalWeeks = (int) ceil($totalDays / 7);
            $subtotal = round($pricePerWeek * $totalWeeks, 2);
        } else {
            $totalMonths = (int) ceil($totalDays / 30);
            $subtotal = round($pricePerMonth * $totalMonths, 2);
        }

        $reservation = Reservation::create([
            'property_id'    => $propiedad->id,
            'user_id'        => Auth::id(),
            'check_in'       => $checkIn,
            'check_in_time'  => $checkInTime,
            'check_out'      => $checkOut,
            'check_out_time' => $checkOutTime,
            'guests'         => 1,
            'price_per_day'  => $pricePerDay,
            'total_days'     => $totalDays,
            'subtotal'       => $subtotal,
            'service_fee'    => 0,
            'total_amount'   => $subtotal,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'notes'          => $notes,
        ]);

        try {
            Mail::to($propiedad->owner->email)->send(new NewReservationNotification($propiedad->owner, $propiedad));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Reservation] Mail failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }

        return ['reservation' => $reservation];
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $reservation->load(['property.images', 'property.owner', 'payment']);
        return view('reservations.show', compact('reservation'));
    }

    public function payment(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        abort_if($reservation->isPaid(), 404);
        $reservation->load(['property', 'payment']);
        return view('reservations.payment', compact('reservation'));
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        abort_if($reservation->isCancelled() || $reservation->isConfirmed() && $reservation->check_in->isPast(), 403);

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelada por el cliente',
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reserva cancelada correctamente.');
    }

    public function myReservations()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->with(['property.images', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reservations.my-reservations', compact('reservations'));
    }
}
