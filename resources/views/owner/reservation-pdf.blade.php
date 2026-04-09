<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }

        .header { background: #4f46e5; color: #fff; padding: 24px 32px; margin-bottom: 24px; }
        .header h1 { font-size: 20px; font-weight: bold; }
        .header p { font-size: 12px; color: #c7d2fe; margin-top: 4px; }

        .container { padding: 0 32px 32px; }

        .section { margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .section-title { background: #f9fafb; padding: 10px 16px; font-size: 10px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
        .section-body { padding: 14px 16px; }

        .grid-2 { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 16px; }
        .col:last-child { padding-right: 0; }

        .label { font-size: 10px; color: #9ca3af; margin-bottom: 2px; }
        .value { font-size: 13px; color: #111827; font-weight: 500; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: bold; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-gray   { background: #f3f4f6; color: #374151; }

        .price-row { display: table; width: 100%; padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
        .price-row:last-child { border-bottom: none; }
        .price-label { display: table-cell; color: #6b7280; }
        .price-value { display: table-cell; text-align: right; font-weight: 500; color: #111827; }
        .price-row.discount .price-label,
        .price-row.discount .price-value { color: #16a34a; }
        .price-row.total { border-top: 2px solid #e5e7eb; margin-top: 4px; }
        .price-row.total .price-label,
        .price-row.total .price-value { font-size: 14px; font-weight: bold; color: #111827; }

        .services-table { width: 100%; border-collapse: collapse; }
        .services-table th { text-align: left; font-size: 10px; color: #9ca3af; text-transform: uppercase; padding: 0 0 6px; border-bottom: 1px solid #e5e7eb; }
        .services-table td { padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
        .services-table td:last-child { text-align: right; }
        .services-table tr:last-child td { border-bottom: none; }

        .footer { margin-top: 32px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>

<div class="header">
    <h1>Reserva #{{ $reservation->id }}</h1>
    <p>{{ $reservation->property->name }} &mdash; Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>

<div class="container">

    {{-- Propiedad --}}
    <div class="section">
        <div class="section-title">Propiedad</div>
        <div class="section-body">
            <div class="value" style="font-size:14px">{{ $reservation->property->name }}</div>
            @if($reservation->property->city)
            <div class="label" style="margin-top:4px">{{ $reservation->property->city }}</div>
            @endif
        </div>
    </div>

    {{-- Cliente --}}
    <div class="section">
        <div class="section-title">Cliente</div>
        <div class="section-body">
            <div class="grid-2">
                <div class="col">
                    <div class="label">Nombre</div>
                    <div class="value">{{ $reservation->user->full_name }}</div>
                </div>
                <div class="col">
                    <div class="label">Email</div>
                    <div class="value">{{ $reservation->user->email }}</div>
                </div>
            </div>
            @if($reservation->user->phone || $reservation->user->dni)
            <div class="grid-2" style="margin-top:10px">
                @if($reservation->user->phone)
                <div class="col">
                    <div class="label">Teléfono</div>
                    <div class="value">{{ $reservation->user->phone }}</div>
                </div>
                @endif
                @if($reservation->user->dni)
                <div class="col">
                    <div class="label">DNI</div>
                    <div class="value">{{ $reservation->user->dni }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Fechas --}}
    <div class="section">
        <div class="section-title">Fechas y huéspedes</div>
        <div class="section-body">
            <div class="grid-2">
                <div class="col">
                    <div class="label">Check-in</div>
                    <div class="value">{{ $reservation->check_in->format('d/m/Y') }}
                        @if($reservation->check_in_time) &mdash; {{ $reservation->check_in_time }}h @endif
                    </div>
                </div>
                <div class="col">
                    <div class="label">Check-out</div>
                    <div class="value">{{ $reservation->check_out->format('d/m/Y') }}
                        @if($reservation->check_out_time) &mdash; {{ $reservation->check_out_time }}h @endif
                    </div>
                </div>
            </div>
            <div class="grid-2" style="margin-top:10px">
                <div class="col">
                    <div class="label">Duración</div>
                    <div class="value">{{ $reservation->total_days }} día(s)</div>
                </div>
                <div class="col">
                    <div class="label">Huéspedes</div>
                    <div class="value">{{ $reservation->guests }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado --}}
    <div class="section">
        <div class="section-title">Estado</div>
        <div class="section-body">
            <div class="grid-2">
                <div class="col">
                    <div class="label">Reserva</div>
                    <div style="margin-top:3px">
                        @php
                            $statusColors = ['pending'=>'yellow','confirmed'=>'green','cancelled'=>'red','completed'=>'blue'];
                            $statusLabels = ['pending'=>'Pendiente','confirmed'=>'Confirmada','cancelled'=>'Cancelada','completed'=>'Completada'];
                            $sc = $statusColors[$reservation->status] ?? 'gray';
                        @endphp
                        <span class="badge badge-{{ $sc }}">{{ $statusLabels[$reservation->status] ?? $reservation->status }}</span>
                    </div>
                </div>
                <div class="col">
                    <div class="label">Pago</div>
                    <div style="margin-top:3px">
                        @php
                            $payColors  = ['unpaid'=>'yellow','paid'=>'green','refunded'=>'blue'];
                            $payLabels  = ['unpaid'=>'Pendiente','paid'=>'Pagado','refunded'=>'Reembolsado'];
                            $pc = $payColors[$reservation->payment_status] ?? 'gray';
                        @endphp
                        <span class="badge badge-{{ $pc }}">{{ $payLabels[$reservation->payment_status] ?? $reservation->payment_status }}</span>
                    </div>
                </div>
            </div>
            @if($reservation->payment_method)
            <div style="margin-top:10px">
                <div class="label">Medio de pago</div>
                <div class="value">
                    @php $pmLabels = ['transfer'=>'Transferencia','cash'=>'Efectivo','credit'=>'Crédito']; @endphp
                    {{ $pmLabels[$reservation->payment_method] ?? $reservation->payment_method }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Servicios adicionales --}}
    @if($reservation->services && $reservation->services->count())
    <div class="section">
        <div class="section-title">Servicios adicionales</div>
        <div class="section-body">
            <table class="services-table">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th style="text-align:right">Cant.</th>
                        <th style="text-align:right">Precio unit.</th>
                        <th style="text-align:right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservation->services as $s)
                    <tr>
                        <td>{{ $s->propertyService->name ?? '—' }}</td>
                        <td style="text-align:right">{{ $s->quantity }}</td>
                        <td style="text-align:right">${{ number_format($s->price, 0, ',', '.') }}</td>
                        <td style="text-align:right">${{ number_format($s->price * $s->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Resumen de precios --}}
    <div class="section">
        <div class="section-title">Resumen de precios</div>
        <div class="section-body">
            @php $bd = $reservation->price_breakdown; @endphp
            @if($bd)
                @if($bd['type'] === 'hourly')
                <div class="price-row">
                    <div class="price-label">{{ $bd['hours'] }} hs &times; ${{ number_format($bd['price_per_hour'], 0, ',', '.') }}/h</div>
                    <div class="price-value">${{ number_format($bd['subtotal'], 0, ',', '.') }}</div>
                </div>
                @else
                @php
                    $days        = collect($bd['days']);
                    $totalNights = $days->count();
                    $basePerNight= $days->first()['base_price'];
                    $baseTotal   = $days->sum('base_price');
                    $discGroups  = $days->filter(fn($d) => ($d['discount_pct'] ?? 0) > 0)
                                       ->groupBy(fn($d) => ($d['discount_pct']).'|'.($d['discount_reason'] ?? ''));
                    $durDiscPct  = $bd['duration_discount_pct'] ?? 0;
                    $savedDur    = round($days->sum('price') - $bd['subtotal'], 2);
                @endphp
                <div class="price-row">
                    <div class="price-label">{{ $totalNights }} días &times; ${{ number_format($basePerNight, 0, ',', '.') }}/día</div>
                    <div class="price-value">${{ number_format($baseTotal, 0, ',', '.') }}</div>
                </div>
                @foreach($discGroups as $group)
                @php
                    $first   = $group->first();
                    $count   = $group->count();
                    $discPct = $first['discount_pct'];
                    $saved   = $group->sum(fn($d) => $d['base_price'] - $d['price']);
                @endphp
                <div class="price-row discount">
                    <div class="price-label">{{ $count === 1 ? $first['date'].' ('.$first['weekday'].')' : $count.' días' }} &bull; -{{ $discPct }}%</div>
                    <div class="price-value">-${{ number_format($saved, 0, ',', '.') }}</div>
                </div>
                @endforeach
                @if($durDiscPct > 0)
                <div class="price-row discount">
                    <div class="price-label">{{ $totalNights }} días &bull; -{{ $durDiscPct }}% por estadía</div>
                    <div class="price-value">-${{ number_format($savedDur, 0, ',', '.') }}</div>
                </div>
                @endif
                @endif
            @else
            <div class="price-row">
                <div class="price-label">{{ $reservation->total_days }} día(s) &times; ${{ number_format($reservation->price_per_day, 0, ',', '.') }}/día</div>
                <div class="price-value">${{ number_format($reservation->subtotal, 0, ',', '.') }}</div>
            </div>
            @endif

            <div class="price-row" style="margin-top:6px; padding-top:6px; border-top:1px solid #e5e7eb;">
                <div class="price-label">Subtotal</div>
                <div class="price-value">${{ number_format($reservation->subtotal, 0, ',', '.') }}</div>
            </div>
            @if($reservation->service_fee > 0)
            <div class="price-row">
                <div class="price-label">Cargo servicio</div>
                <div class="price-value">${{ number_format($reservation->service_fee, 0, ',', '.') }}</div>
            </div>
            @endif
            @if($reservation->services && $reservation->services->count())
            @php $serviciosTotal = $reservation->services->sum(fn($s) => $s->price * $s->quantity); @endphp
            @if($serviciosTotal > 0)
            <div class="price-row">
                <div class="price-label">Servicios adicionales</div>
                <div class="price-value">${{ number_format($serviciosTotal, 0, ',', '.') }}</div>
            </div>
            @endif
            @endif
            @foreach($reservation->extraCosts as $ec)
            <div class="price-row">
                <div class="price-label">{{ $ec->name }}</div>
                <div class="price-value">${{ number_format($ec->price, 0, ',', '.') }}</div>
            </div>
            @endforeach
            @foreach($reservation->discounts as $d)
            <div class="price-row discount">
                <div class="price-label">{{ $d->name }} (descuento)</div>
                <div class="price-value">-${{ number_format($d->price, 0, ',', '.') }}</div>
            </div>
            @endforeach
            <div class="price-row total" style="padding-top:8px; margin-top:6px">
                <div class="price-label">TOTAL</div>
                <div class="price-value">${{ number_format($reservation->total_amount, 0, ',', '.') }} ARS</div>
            </div>
        </div>
    </div>

    {{-- Notas --}}
    @if($reservation->notes)
    <div class="section">
        <div class="section-title">Notas</div>
        <div class="section-body">
            <div style="color:#374151; line-height:1.6">{{ $reservation->notes }}</div>
        </div>
    </div>
    @endif

</div>

<div class="footer">
    Reserva #{{ $reservation->id }} &mdash; {{ $reservation->property->name }} &mdash; {{ config('app.name') }}
</div>

</body>
</html>
