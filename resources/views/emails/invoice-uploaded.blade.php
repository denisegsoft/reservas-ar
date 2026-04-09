<x-mail::message>
# Tu factura está disponible

Hola, **{{ $reservation->user->name }}**.

El propietario de **{{ $reservation->property->name }}** subió la factura correspondiente a tu reserva.

| | |
|---|---|
| **Check-in** | {{ $reservation->check_in->format('d/m/Y') }} |
| **Check-out** | {{ $reservation->check_out->format('d/m/Y') }} |
| **Total** | ${{ number_format($reservation->total_amount, 0, ',', '.') }} |

Podés descargarla desde el detalle de tu reserva.

<x-mail::button :url="route('reservations.show', $reservation)">
Ver mi reserva
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
