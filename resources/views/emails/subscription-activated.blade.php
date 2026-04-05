<x-mail::message>
# ¡Felicitaciones, {{ $owner->name }}!

Tu pago fue procesado correctamente y tu suscripción en **{{ config('app.name') }}** ya está activa.

A partir de ahora tenés acceso completo:

- Ver los **datos de contacto** de tus clientes en cada reserva
- Leer y **responder mensajes**
- Gestionar tus propiedades sin restricciones

<x-mail::button :url="route('owner.dashboard')">
Ir a mi panel
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
