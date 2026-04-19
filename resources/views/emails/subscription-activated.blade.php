<x-mail::message>
# ¡Felicitaciones, {{ $owner->name }}!

Tu pago fue procesado correctamente y tu suscripción en **{{ config('app.name') }}** ya está activa.

---

## Lo que ya podés usar hoy

📞 **Contacto directo con clientes** — Ves los datos de contacto de cada persona que reserva

💬 **Mensajes desbloqueados** — Leé y respondé todos los mensajes sin restricciones

📈 **Panel completo** — Gestioná propiedades, reservas, clientes y ganancias en un solo lugar

💰 **Concretá más ventas** — Posicionamiento, marketing y asesoría para atraer más clientes

<x-mail::button :url="route('owner.dashboard')">
Ir a mi panel
</x-mail::button>

---

## Próximamente nos contactamos para entregarte

Estamos preparando todo para brindarte los siguientes servicios. Te escribiremos al email registrado para coordinar los detalles:

🤖 **Chatbot inteligente para tu WhatsApp Business** — Configuramos un Chatbot inteligente inteligente para que tu negocio atienda clientes las 24 hs automáticamente, incluso cuando no estás disponible

🌐 **Sitio web profesional** — Creamos tu página web a medida para mostrar tu negocio al mundo y atraer más reservas

---

Gracias por confiar en nosotros. ¡Estamos con vos en cada paso!

Saludos,
{{ config('app.name') }}
</x-mail::message>
