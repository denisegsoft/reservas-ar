<x-mail::message>
# ¡Tu estadía es en 2 días!

Hola, **{{ $reservation->user->name }}**.

Te recordamos que en 2 días comenzará tu estadía en **{{ $reservation->property->name }}**.

| | |
|---|---|
| **Check-in** | {{ $reservation->check_in->format('d/m/Y') }}{{ $reservation->check_in_time ? ' a las ' . $reservation->check_in_time : '' }} |
| **Check-out** | {{ $reservation->check_out->format('d/m/Y') }}{{ $reservation->check_out_time ? ' a las ' . $reservation->check_out_time : '' }} |
| **Capacidad** | {{ $reservation->guests }} |

@if($reservation->property->owner->phone)
**Contacto del propietario:** {{ $reservation->property->owner->phone }}
@endif

<x-mail::button :url="route('reservations.show', $reservation)">
Ver mi reserva
</x-mail::button>

¡Que tengas una excelente estadía!

Saludos,
{{ config('app.name') }}
</x-mail::message>
