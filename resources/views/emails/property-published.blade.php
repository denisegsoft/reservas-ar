<x-mail::message>
# ¡Tu propiedad ya está publicada!

Hola **{{ $property->owner->name }}**,

Tu propiedad **{{ $property->name }}** ya está visible en {{ config('app.name') }} y los clientes pueden encontrarla.

<x-mail::button :url="route('properties.show', $property->slug)">
Ver mi propiedad
</x-mail::button>

---

## Activá tu suscripción y aprovechá al máximo

Con un **único pago de ${{ number_format($subscriptionPrice, 0, ',', '.') }} ARS, sin renovaciones**, desbloqueás todo lo que necesitás para gestionar tus reservas y hacer crecer tu negocio:

💰 **Concretá tus ventas** — Contacto directo con tus clientes y posibles clientes, marketing, posicionamiento y asesoría de ventas

📞 **Recibí reservas** — Los clientes se comunican directamente con vos

📈 **Panel completo** — Gestioná propiedades, reservas, clientes y ganancias

🤖 **Chatbot para tu WhatsApp Business** — Automatizá las respuestas de tu empresa y atendé a tus clientes las 24 hs

🌐 **Sitio web profesional** — Creamos tu página web a medida para mostrar tu negocio al mundo

🎯 **Asesoría en marketing** — Te ayudamos a posicionar tu negocio, atraer más clientes y crecer en redes sociales con estrategia real


<x-mail::button :url="$activationUrl" color="green">
Activar mi suscripción
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
