<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class PricingService
{
    private const WEEKDAY_NAMES = [0=>'Dom', 1=>'Lun', 2=>'Mar', 3=>'Mié', 4=>'Jue', 5=>'Vie', 6=>'Sáb'];

    /**
     * Calculate a price breakdown from date/time data without touching any model.
     * Returns ['breakdown', 'subtotal', 'totalDays', 'pricePerDay']
     *      or ['error' => [field => message]] on validation failure.
     */
    public function calculate(array $data, Property $propiedad): array
    {
        $checkInDt  = Carbon::parse($data['check_in']  . ' ' . ($data['check_in_time']  ?? '14:00'));
        $checkOutDt = Carbon::parse($data['check_out'] . ' ' . ($data['check_out_time'] ?? '11:00'));
        $totalHours = (int) $checkInDt->diffInHours($checkOutDt);
        $totalDays  = (int) $checkInDt->copy()->startOfDay()->diffInDays($checkOutDt->copy()->startOfDay()) ?: 1;

        if ($totalHours <= 0) {
            return ['error' => ['check_out' => 'La fecha y hora de salida debe ser posterior a la de entrada.']];
        }
        if ($propiedad->min_days && $totalDays < $propiedad->min_days) {
            return ['error' => ['check_out' => "La estadía mínima es de {$propiedad->min_days} día(s)."]];
        }

        $pricePerDay  = $propiedad->price_per_day;
        $pricePerHour = $propiedad->price_per_hour;

        if ($checkInDt->isSameDay($checkOutDt)) {
            $subtotal  = round((float)$pricePerHour * $totalHours, 2);
            $breakdown = [
                'type'           => 'hourly',
                'hours'          => $totalHours,
                'price_per_hour' => $pricePerHour,
                'subtotal'       => $subtotal,
            ];
        } else {
            [$breakdown, $subtotal] = $this->dailyBreakdown(
                $checkInDt, $checkOutDt, $totalDays, (float) $pricePerDay,
                $propiedad->date_discounts ?? [],
                $propiedad->weekday_discounts ?? [],
                $propiedad->day_discounts ?? []
            );
        }

        return compact('breakdown', 'subtotal', 'totalDays', 'pricePerDay');
    }

    /**
     * Recalculate and persist breakdown on an existing reservation (skips confirmed ones).
     */
    public function recalculate(Reservation $reservation): void
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

        $pricePerDay  = (float) $reservation->price_per_day;
        $pricePerHour = (float) ($propiedad->price_per_hour ?? 0);

        if ($checkInDt->isSameDay($checkOutDt)) {
            $subtotal  = round($pricePerHour * $totalHours, 2);
            $breakdown = [
                'type'           => 'hourly',
                'hours'          => $totalHours,
                'price_per_hour' => $pricePerHour,
                'subtotal'       => $subtotal,
            ];
        } else {
            [$breakdown, $subtotal] = $this->dailyBreakdown(
                $checkInDt, $checkOutDt, $totalDays, $pricePerDay,
                $propiedad->date_discounts ?? [],
                $propiedad->weekday_discounts ?? [],
                $propiedad->day_discounts ?? []
            );
        }

        $serviciosTotal  = $reservation->relationLoaded('services')
            ? $reservation->services->sum(fn($s) => $s->price * $s->quantity) : 0;
        $extraCostsTotal = $reservation->relationLoaded('extraCosts')
            ? $reservation->extraCosts->sum('price') : 0;
        $discountsTotal  = $reservation->relationLoaded('discounts')
            ? $reservation->discounts->sum('price') : 0;

        $total = round($breakdown['subtotal'] + ($reservation->service_fee ?? 0) + $serviciosTotal + $extraCostsTotal - $discountsTotal, 2);

        $reservation->update([
            'price_breakdown' => $breakdown,
            'subtotal'        => $breakdown['subtotal'],
            'total_days'      => $totalDays,
            'total_amount'    => $total,
        ]);

        // Keep in-memory attributes in sync so the view reflects the recalculated values
        $reservation->price_breakdown = $breakdown;
        $reservation->subtotal        = $breakdown['subtotal'];
        $reservation->total_days      = $totalDays;
        $reservation->total_amount    = $total;
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function dailyBreakdown(
        Carbon $checkInDt,
        Carbon $checkOutDt,
        int $totalDays,
        float $pricePerDay,
        array $dateDiscounts,
        array $weekdayDiscounts,
        array $durationDiscounts
    ): array {
        $daysDetail   = [];
        $subtotal     = 0.0;
        $cursor       = $checkInDt->copy()->startOfDay();
        $checkOutDate = $checkOutDt->copy()->startOfDay();

        while ($cursor->lt($checkOutDate)) {
            $weekday = (int) $cursor->dayOfWeek;
            [$dayDiscount, $discountReason] = $this->bestDayDiscount(
                $cursor, $weekday, $dateDiscounts, $weekdayDiscounts
            );

            $dayPrice  = $dayDiscount > 0 ? round($pricePerDay * (1 - $dayDiscount / 100), 2) : $pricePerDay;
            $subtotal += $dayPrice;

            $daysDetail[] = [
                'date'            => $cursor->format('d/m/Y'),
                'weekday'         => self::WEEKDAY_NAMES[$weekday],
                'base_price'      => $pricePerDay,
                'discount_pct'    => $dayDiscount,
                'discount_reason' => $discountReason,
                'price'           => $dayPrice,
            ];

            $cursor->addDay();
        }

        $subtotal            = round($subtotal, 2);
        $durationDiscountPct = 0.0;

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

        return [$breakdown, $subtotal];
    }

    private function bestDayDiscount(Carbon $cursor, int $weekday, array $dateDiscounts, array $weekdayDiscounts): array
    {
        $dayDiscount    = 0.0;
        $discountReason = null;

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

        return [$dayDiscount, $discountReason];
    }
}
