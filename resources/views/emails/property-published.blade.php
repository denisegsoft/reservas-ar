<x-mail::message>
# ¡Tu propiedad ya está publicada!

Hola **{{ $property->owner->name }}**,

Tu propiedad **{{ $property->name }}** ya está visible en {{ config('app.name') }} y los clientes pueden encontrarla.

<x-mail::button :url="route('properties.show', $property->slug)" color="primary">
Ver mi propiedad
</x-mail::button>

---

## Activá tu suscripción y aprovechá al máximo

@if($subscriptionDiscount)
🏷️ **Oferta de lanzamiento: {{ $subscriptionDiscount['pct'] }}% OFF{{ $subscriptionDiscount['label'] ? ' · ' . $subscriptionDiscount['label'] : '' }}**

Hoy podés activar tu cuenta por solo **${{ number_format($subscriptionPrice, 0, ',', '.') }} ARS** (precio regular ~~${{ number_format($subscriptionBasePrice, 0, ',', '.') }} ARS~~). Pago único, sin renovaciones.

¡No dejes pasar esta oportunidad!
@else
Con un **único pago de ${{ number_format($subscriptionPrice, 0, ',', '.') }} ARS, sin renovaciones**, desbloqueás todo lo que necesitás para gestionar tus reservas y hacer crecer tu negocio.
@endif

💰 **Concretá más ventas** — Nos encargaremos de conectarte con más clientes y concretar reservas

📈 **Panel personalizado** — Gestioná propiedades, reservas, clientes y ganancias desde un solo lugar

🤖 **Chatbot inteligente para tu WhatsApp Business** — Automatizá las respuestas de tu empresa y atendé a tus clientes las 24 hs

🌐 **Sitio web propio** — Creamos tu página web a medida para más presencia online

<x-mail::button :url="$activationUrl" color="green">
@if($subscriptionDiscount)
Activar con {{ $subscriptionDiscount['pct'] }}% OFF — ${{ number_format($subscriptionPrice, 0, ',', '.') }} ARS
@else
Activar mi suscripción
@endif
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
