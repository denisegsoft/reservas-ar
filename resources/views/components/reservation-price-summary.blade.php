@props(['reservation', 'recalculate' => false])

@php $bd = $reservation->price_breakdown; @endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Resumen de precios</h2>
        @if($recalculate)
        <button type="submit"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Recalcular
        </button>
        @endif
    </div>

    @if($bd)
        @if($bd['type'] === 'hourly')
            <div class="flex justify-between text-sm text-gray-600 mb-3">
                <span>{{ $bd['hours'] }} hs × ${{ number_format($bd['price_per_hour'], 0, ',', '.') }}/h</span>
                <span class="font-medium text-gray-800">${{ number_format($bd['subtotal'], 0, ',', '.') }}</span>
            </div>
        @else
            @php
                $days         = collect($bd['days']);
                $totalNights  = $days->count();
                $basePerNight = $days->first()['base_price'];
                $baseTotal    = $days->sum('base_price');

                // Días con descuento agrupados por (pct, reason)
                $discountedGroups = $days->filter(fn($d) => ($d['discount_pct'] ?? 0) > 0)
                    ->groupBy(fn($d) => ($d['discount_pct']) . '|' . ($d['discount_reason'] ?? ''));

                $subtotalBeforeDuration = $days->sum('price');
                $durationDiscountPct    = $bd['duration_discount_pct'] ?? 0;
                $savedDuration          = round($subtotalBeforeDuration - $bd['subtotal'], 2);
            @endphp

            <div class="space-y-1.5 mb-3">
                {{-- Línea base --}}
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">
                        {{ $totalNights }} {{ $totalNights === 1 ? 'día' : 'días' }}
                        × ${{ number_format($basePerNight, 0, ',', '.') }}/día
                    </span>
                    <span class="font-medium text-gray-700">${{ number_format($baseTotal, 0, ',', '.') }}</span>
                </div>

                {{-- Descuentos por día/fecha --}}
                @foreach($discountedGroups as $group)
                    @php
                        $first      = $group->first();
                        $count      = $group->count();
                        $discPct    = $first['discount_pct'];
                        $discReason = $first['discount_reason'] ?? null;
                        $saved      = $group->sum(fn($d) => $d['base_price'] - $d['price']);
                    @endphp
                    <div class="flex justify-between text-sm text-red-500">
                        <span>
                            {{ $count === 1 ? $first['date'] . ' (' . $first['weekday'] . ')' : $count . ' días' }}
                            · -{{ $discPct }}%
                            @if($discReason) <span class="text-red-400">{{ $discReason }}</span> @endif
                        </span>
                        <span>-${{ number_format($saved, 0, ',', '.') }}</span>
                    </div>
                @endforeach

                {{-- Descuento por duración --}}
                @if($durationDiscountPct > 0)
                    <div class="flex justify-between text-sm text-red-500">
                        <span>{{ $totalNights }} días · -{{ $durationDiscountPct }}% por estadía</span>
                        <span>-${{ number_format($savedDuration, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="flex justify-between text-sm text-gray-600 mb-1.5">
            <span>{{ $reservation->total_days }} día(s) × ${{ number_format($reservation->price_per_day, 0, ',', '.') }}/día</span>
            <span class="font-medium text-gray-800">${{ number_format($reservation->subtotal, 0, ',', '.') }}</span>
        </div>
    @endif

    <div class="border-t border-gray-100 pt-3 space-y-1.5 mt-1">
        <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>${{ number_format($reservation->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($reservation->service_fee > 0)
        <div class="flex justify-between text-sm text-gray-600">
            <span>Cargo servicio</span>
            <span>${{ number_format($reservation->service_fee, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($reservation->services && $reservation->services->count())
            @php $serviciosTotal = $reservation->services->sum(fn($s) => $s->price * $s->quantity); @endphp
            @if($serviciosTotal > 0)
            <div class="flex justify-between text-sm text-gray-600">
                <span>Servicios adicionales</span>
                <span>${{ number_format($serviciosTotal, 0, ',', '.') }}</span>
            </div>
            @endif
        @endif
        @if($reservation->relationLoaded('extraCosts') && $reservation->extraCosts->count())
            @foreach($reservation->extraCosts as $ec)
            <div class="flex justify-between text-sm text-gray-600">
                <span>{{ $ec->name }}</span>
                <span>${{ number_format($ec->price, 0, ',', '.') }}</span>
            </div>
            @endforeach
        @endif
        @if($reservation->relationLoaded('discounts') && $reservation->discounts->count())
            @foreach($reservation->discounts as $d)
            <div class="flex justify-between text-sm text-green-600">
                <span>{{ $d->name }} <span class="text-green-400 text-xs">(descuento)</span></span>
                <span>-${{ number_format($d->price, 0, ',', '.') }}</span>
            </div>
            @endforeach
        @endif
        <div class="flex justify-between font-bold text-gray-900 border-t border-gray-100 pt-2 text-base">
            <span>Total</span>
            <span>${{ number_format($reservation->total_amount, 0, ',', '.') }} ARS</span>
        </div>
    </div>
</div>
