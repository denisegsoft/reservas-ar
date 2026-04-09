<x-mail::message>
# ¿Cómo fue tu estadía?

Hola, **{{ $reservation->user->name }}**.

Esperamos que hayas disfrutado tu estadía en **{{ $reservation->property->name }}**.

Tu opinión es muy importante para ayudar a otros viajeros. ¡Te tomará solo un minuto!

<x-mail::button :url="route('reservations.show', $reservation)">
Dejar mi reseña
</x-mail::button>

Gracias por elegirnos.

Saludos,
{{ config('app.name') }}
</x-mail::message>
