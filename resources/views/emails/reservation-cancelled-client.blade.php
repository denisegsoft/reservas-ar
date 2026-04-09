<x-mail::message>
# Tu reserva fue cancelada

Hola, **{{ $reservation->user->name }}**.

Lamentamos informarte que tu reserva en **{{ $reservation->property->name }}** fue cancelada.

| | |
|---|---|
| **Check-in** | {{ $reservation->check_in->format('d/m/Y') }} |
| **Check-out** | {{ $reservation->check_out->format('d/m/Y') }} |
@if($reservation->cancellation_reason)
| **Motivo** | {{ $reservation->cancellation_reason }} |
@endif

Si tenés alguna consulta, podés comunicarte con el propietario a través de la plataforma.

<x-mail::button :url="route('properties.index')">
Ver otras propiedades
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
