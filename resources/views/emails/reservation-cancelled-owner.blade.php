<x-mail::message>
# Una reserva fue cancelada

Hola, **{{ $reservation->property->owner->name }}**.

El cliente **{{ $reservation->user->name }}** canceló su reserva en **{{ $reservation->property->name }}**.

| | |
|---|---|
| **Check-in** | {{ $reservation->check_in->format('d/m/Y') }} |
| **Check-out** | {{ $reservation->check_out->format('d/m/Y') }} |
| **Huéspedes** | {{ $reservation->guests }} |
| **Total** | ${{ number_format($reservation->total_amount, 0, ',', '.') }} |

Las fechas quedaron liberadas y están disponibles para nuevas reservas.

<x-mail::button :url="route('owner.reservations')">
Ver mis reservas
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
