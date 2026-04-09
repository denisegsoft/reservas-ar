<x-mail::message>
# ¡Recibiste una nueva reseña!

Hola, **{{ $review->property->owner->name }}**.

**{{ $review->user->name }}** dejó una reseña en tu propiedad **{{ $review->property->name }}**.

> {{ $review->comment }}

**Calificación:** {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }} ({{ $review->rating }}/5)

La reseña está pendiente de aprobación. Podés revisarla desde tu panel.

<x-mail::button :url="route('owner.reservations')">
Ver en mi panel
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
