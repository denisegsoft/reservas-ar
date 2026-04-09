<?php

namespace App\Http\Controllers;

use App\Mail\NewReservationNotification;
use App\Mail\ReservationCancelledOwnerNotification;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationService;
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

    /**
     * Calcula el breakdown de precio sin crear ni modificar ninguna reserva.
     * Retorna ['breakdown'=>..., 'subtotal'=>..., 'total_days'=>..., 'price_per_day'=>...]
     * o ['error' => [...]] si las fechas son inválidas.
     */
    public static function calculateBreakdown(array $data, Property $propiedad): array
    {
        $checkIn      = $data['check_in'];
        $checkOut     = $data['check_out'];
        $checkInTime  = $data['check_in_time']  ?? '14:00';
        $checkOutTime = $data['check_out_time'] ?? '11:00';

        $checkInDt  = Carbon::parse($checkIn  . ' ' . $checkInTime);
        $checkOutDt = Carbon::parse($checkOut . ' ' . $checkOutTime);
        $totalHours = (int) $checkInDt->diffInHours($checkOutDt);
        $totalDays  = (int) $checkInDt->copy()->startOfDay()->diffInDays($checkOutDt->copy()->startOfDay()) ?: 1;

        if ($totalHours <= 0) {
            return ['error' => ['check_out' => 'La fecha y hora de salida debe ser posterior a la de entrada.']];
        }
        if ($totalDays < $propiedad->min_days) {
            return ['error' => ['check_out' => "La estadía mínima es de {$propiedad->min_days} día(s)."]];
        }

        $pricePerDay  = $propiedad->price_per_day;
        $pricePerHour = $propiedad->price_per_hour;
        $sameDay      = $checkInDt->isSameDay($checkOutDt);

        if ($sameDay) {
            $subtotal  = round($pricePerHour * $totalHours, 2);
            $breakdown = [
                'type'           => 'hourly',
                'hours'          => $totalHours,
                'price_per_hour' => $pricePerHour,
                'subtotal'       => $subtotal,
            ];
        } else {
            $dateDiscounts    = $propiedad->date_discounts ?? [];
            $weekdayDiscounts = $propiedad->weekday_discounts ?? [];
            $weekdayNames     = [0=>'Dom',1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb'];

            $daysDetail   = [];
            $subtotal     = 0.0;
            $cursor       = $checkInDt->copy()->startOfDay();
            $checkOutDate = $checkOutDt->copy()->startOfDay();

            while ($cursor->lt($checkOutDate)) {
                $dayDiscount    = 0.0;
                $discountReason = null;
                $weekday        = (int) $cursor->dayOfWeek;

                foreach ($weekdayDiscounts as $tramo) {
                    $wDays = array_map('intval', (array)($tramo['days'] ?? []));
                    if (in_array($weekday, $wDays, true) && (float)$tramo['discount'] > $dayDiscount) {
                        $dayDiscount    = (float)$tramo['discount'];
                        $discountReason = 'Día especial';
                    }
                }
                foreach ($dateDiscounts as $tramo) {
                    if (empty($tramo['date_from']) || empty($tramo['date_to'])) continue;
                    $from = Carbon::parse($tramo['date_from'])->startOfDay();
                    $to   = Carbon::parse($tramo['date_to'])->endOfDay();
                    if ($cursor->between($from, $to) && (float)$tramo['discount'] > $dayDiscount) {
                        $dayDiscount    = (float)$tramo['discount'];
                        $discountReason = 'Fecha especial';
                    }
                }

                $dayPrice  = $dayDiscount > 0 ? round($pricePerDay * (1 - $dayDiscount / 100), 2) : $pricePerDay;
                $subtotal += $dayPrice;

                $daysDetail[] = [
                    'date'            => $cursor->format('d/m/Y'),
                    'weekday'         => $weekdayNames[$weekday],
                    'base_price'      => $pricePerDay,
                    'discount_pct'    => $dayDiscount,
                    'discount_reason' => $discountReason,
                    'price'           => $dayPrice,
                ];
                $cursor->addDay();
            }

            $subtotal            = round($subtotal, 2);
            $durationDiscountPct = 0.0;
            $durationDiscounts   = $propiedad->day_discounts ?? [];

            if (!empty($durationDiscounts)) {
                usort($durationDiscounts, fn($a, $b) => (int)$b['days'] <=> (int)$a['days']);
                foreach ($durationDiscounts as $tramo) {
                    if ($totalDays >= (int)$tramo['days']) {
                        $durationDiscountPct = (float)$tramo['discount'];
                        $subtotal = round($subtotal * (1 - $durationDiscountPct / 100), 2);
                        break;
                    }
                }
            }

            $breakdown = [
                'type'                  => 'daily',
                'days'                  => $daysDetail,
                'duration_discount_pct' => $durationDiscountPct,
                'subtotal'              => $subtotal,
            ];
        }

        return compact('breakdown', 'subtotal', 'totalDays', 'pricePerDay');
    }

public function buildReservation(array $data, Property $propiedad): array
    {
        $checkIn     = $data['check_in'] ?? null;
        $checkOut    = $data['check_out'] ?? null;
        $checkInTime = $data['check_in_time'] ?? null;
        $checkOutTime= $data['check_out_time'] ?? null;
        $notes       = $data['notes'] ?? null;
        $guests      = $data['guests'] ?? null;

        if (!$propiedad->isAvailable($checkIn, $checkOut)) {
            return ['error' => ['check_in' => 'Las fechas seleccionadas no están disponibles.']];
        }

        $calc = static::calculateBreakdown($data, $propiedad);
        if (isset($calc['error'])) {
            return $calc;
        }

        ['breakdown' => $breakdown, 'subtotal' => $subtotal, 'totalDays' => $totalDays, 'pricePerDay' => $pricePerDay] = $calc;

        $reservation = Reservation::create([
            'property_id'     => $propiedad->id,
            'user_id'         => Auth::id(),
            'check_in'        => $checkIn,
            'check_in_time'   => $checkInTime,
            'check_out'       => $checkOut,
            'check_out_time'  => $checkOutTime,
            'guests'          => $guests,
            'price_per_day'   => $pricePerDay,
            'total_days'      => $totalDays,
            'subtotal'        => $subtotal,
            'price_breakdown' => $breakdown,
            'service_fee'     => 0,
            'total_amount'    => $subtotal,
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'notes'           => $notes,
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
        $reservation->load(['property.images', 'property.owner', 'property.services', 'payment', 'services.propertyService']);
        static::ensureBreakdown($reservation);
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
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelada por el cliente',
        ]);

        try {
            $reservation->load(['user', 'property.owner']);
            Mail::to($reservation->property->owner->email)
                ->send(new ReservationCancelledOwnerNotification($reservation));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Reservation] Cancel owner mail failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reserva cancelada correctamente.');
    }

    public function updateServices(Reservation $reservation, Request $request)
    {
        // Only allow while not cancelled
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

    /**
     * Si la reserva no tiene breakdown y no está confirmada, lo calcula y guarda.
     */
    public static function ensureBreakdown(Reservation $reservation): void
    {
        if ($reservation->isConfirmed()) {
            return;
        }

        $propiedad    = $reservation->property;
        $checkInTime  = $reservation->check_in_time  ?? '14:00';
        $checkOutTime = $reservation->check_out_time ?? '11:00';

        $checkInDt  = Carbon::parse($reservation->check_in->format('Y-m-d')  . ' ' . $checkInTime);
        $checkOutDt = Carbon::parse($reservation->check_out->format('Y-m-d') . ' ' . $checkOutTime);
        $totalHours = (int) $checkInDt->diffInHours($checkOutDt);
        $totalDays  = (int) $checkInDt->copy()->startOfDay()->diffInDays($checkOutDt->copy()->startOfDay()) ?: 1;
        $sameDay    = $checkInDt->isSameDay($checkOutDt);

        $pricePerDay  = (float) $reservation->price_per_day;
        $pricePerHour = (float) ($propiedad->price_per_hour ?? 0);

        if ($sameDay) {
            $subtotal  = round($pricePerHour * $totalHours, 2);
            $breakdown = [
                'type'           => 'hourly',
                'hours'          => $totalHours,
                'price_per_hour' => $pricePerHour,
                'subtotal'       => $subtotal,
            ];
        } else {
            $dateDiscounts    = $propiedad->date_discounts    ?? [];
            $weekdayDiscounts = $propiedad->weekday_discounts ?? [];
            $weekdayNames     = [0=>'Dom',1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb'];

            $daysDetail   = [];
            $subtotal     = 0.0;
            $cursor       = $checkInDt->copy()->startOfDay();
            $checkOutDate = $checkOutDt->copy()->startOfDay();

            while ($cursor->lt($checkOutDate)) {
                $dayDiscount    = 0.0;
                $discountReason = null;
                $weekday        = (int) $cursor->dayOfWeek;

                foreach ($weekdayDiscounts as $tramo) {
                    $wDays = array_map('intval', (array)($tramo['days'] ?? []));
                    if (in_array($weekday, $wDays, true) && (float)$tramo['discount'] > $dayDiscount) {
                        $dayDiscount    = (float)$tramo['discount'];
                        $discountReason = 'Día especial';
                    }
                }

                foreach ($dateDiscounts as $tramo) {
                    if (empty($tramo['date_from']) || empty($tramo['date_to'])) continue;
                    $from = Carbon::parse($tramo['date_from'])->startOfDay();
                    $to   = Carbon::parse($tramo['date_to'])->endOfDay();
                    if ($cursor->between($from, $to) && (float)$tramo['discount'] > $dayDiscount) {
                        $dayDiscount    = (float)$tramo['discount'];
                        $discountReason = 'Fecha especial';
                    }
                }

                $dayPrice  = $dayDiscount > 0
                    ? round($pricePerDay * (1 - $dayDiscount / 100), 2)
                    : $pricePerDay;
                $subtotal += $dayPrice;

                $daysDetail[] = [
                    'date'            => $cursor->format('d/m/Y'),
                    'weekday'         => $weekdayNames[$weekday],
                    'base_price'      => $pricePerDay,
                    'discount_pct'    => $dayDiscount,
                    'discount_reason' => $discountReason,
                    'price'           => $dayPrice,
                ];

                $cursor->addDay();
            }

            $subtotal            = round($subtotal, 2);
            $durationDiscountPct = 0.0;

            $durationDiscounts = $propiedad->day_discounts ?? [];
            if (!empty($durationDiscounts)) {
                usort($durationDiscounts, fn($a, $b) => (int)$b['days'] <=> (int)$a['days']);
                foreach ($durationDiscounts as $tramo) {
                    if ($totalDays >= (int)$tramo['days']) {
                        $durationDiscountPct = (float)$tramo['discount'];
                        $subtotal = round($subtotal * (1 - $durationDiscountPct / 100), 2);
                        break;
                    }
                }
            }

            $breakdown = [
                'type'                  => 'daily',
                'days'                  => $daysDetail,
                'duration_discount_pct' => $durationDiscountPct,
                'subtotal'              => $subtotal,
            ];
        }

        $serviciosTotal  = $reservation->relationLoaded('services')
            ? $reservation->services->sum(fn($s) => $s->price * $s->quantity)
            : 0;
        $extraCostsTotal = $reservation->relationLoaded('extraCosts')
            ? $reservation->extraCosts->sum('price')
            : 0;
        $discountsTotal  = $reservation->relationLoaded('discounts')
            ? $reservation->discounts->sum('price')
            : 0;
        $total = round($breakdown['subtotal'] + ($reservation->service_fee ?? 0) + $serviciosTotal + $extraCostsTotal - $discountsTotal, 2);

        $reservation->update([
            'price_breakdown' => $breakdown,
            'subtotal'        => $breakdown['subtotal'],
            'total_days'      => $totalDays,
            'total_amount'    => $total,
        ]);
        $reservation->price_breakdown = $breakdown;
        $reservation->subtotal        = $breakdown['subtotal'];
        $reservation->total_days      = $totalDays;
        $reservation->total_amount    = $total;
    }
}
