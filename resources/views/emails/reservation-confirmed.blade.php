<x-mail::message>
# ¡Tu reserva está confirmada!

Hola, **{{ $reservation->user->name }}**.

Tu reserva en **{{ $reservation->property->name }}** fue confirmada exitosamente.

| | |
|---|---|
| **Check-in** | {{ $reservation->check_in->format('d/m/Y') }}{{ $reservation->check_in_time ? ' a las ' . $reservation->check_in_time : '' }} |
| **Check-out** | {{ $reservation->check_out->format('d/m/Y') }}{{ $reservation->check_out_time ? ' a las ' . $reservation->check_out_time : '' }} |
| **Huéspedes** | {{ $reservation->guests }} |
| **Total** | ${{ number_format($reservation->total_amount, 0, ',', '.') }} |

<x-mail::button :url="route('reservations.show', $reservation)">
Ver mi reserva
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
