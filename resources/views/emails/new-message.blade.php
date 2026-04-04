<x-mail::message>
# ¡Hola, {{ $recipient->name }}!

**{{ $sender->name }}** te envió un nuevo mensaje en {{ config('app.name') }}.

Ingresá a tu cuenta para leerlo y responder.

<x-mail::button :url="route('messages.index')">
Ver mis mensajes
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
