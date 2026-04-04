<x-mail::message>
# ¡Hola, {{ $owner->name }}!

Recibiste una nueva solicitud de reserva en {{ config('app.name') }}.

Ingresá a tu panel para ver los detalles y gestionarla.

<x-mail::button :url="route('owner.reservations')">
Ver mis reservas
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
