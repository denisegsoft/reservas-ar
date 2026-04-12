# ReservaQuintas — Documentación del Sistema

> Plataforma de alquiler de quintas y propiedades para eventos en Argentina.
> Stack: Laravel 12 · PHP 8.4 · MySQL · Tailwind CSS · Alpine.js · MercadoPago

---

## Tabla de Contenidos

1. [Arquitectura General](#1-arquitectura-general)
2. [Roles de Usuario](#2-roles-de-usuario)
3. [Módulos del Sistema](#3-módulos-del-sistema)
   - [3.1 Autenticación y Perfil](#31-autenticación-y-perfil)
   - [3.2 Propiedades](#32-propiedades)
   - [3.3 Reservas](#33-reservas)
   - [3.4 Pagos (MercadoPago)](#34-pagos-mercadopago)
   - [3.5 Suscripción de Propietario](#35-suscripción-de-propietario)
   - [3.6 Mensajes](#36-mensajes)
   - [3.7 Reseñas](#37-reseñas)
   - [3.8 Favoritos](#38-favoritos)
   - [3.9 Panel de Propietario](#39-panel-de-propietario)
   - [3.10 Panel de Administración](#310-panel-de-administración)
   - [3.11 Soporte y Sugerencias](#311-soporte-y-sugerencias)
   - [3.12 Geo API](#312-geo-api)
4. [Modelos y Base de Datos](#4-modelos-y-base-de-datos)
5. [Sistema de Precios](#5-sistema-de-precios)
6. [Sistema de Caché](#6-sistema-de-caché)
7. [Emails del Sistema](#7-emails-del-sistema)
8. [Rutas Completas](#8-rutas-completas)
9. [Guía de Testing Manual](#9-guía-de-testing-manual)

---

## 1. Arquitectura General

```
reserva-quintas/
├── app/
│   ├── Http/Controllers/
│   │   ├── HomeController.php          — Página de inicio (cacheada)
│   │   ├── PropertyController.php      — CRUD de propiedades + búsqueda
│   │   ├── ReservationController.php   — Reservas del cliente
│   │   ├── PaymentController.php       — Pagos MP + webhook
│   │   ├── SubscriptionController.php  — Suscripción de propietarios
│   │   ├── MessageController.php       — Mensajes entre usuarios
│   │   ├── ReviewController.php        — Reseñas de propiedades
│   │   ├── FavoriteController.php      — Lista de favoritos
│   │   ├── SuggestionController.php    — Formulario de sugerencias
│   │   ├── SupportController.php       — Tickets de soporte
│   │   ├── GeoController.php           — API partidos/localidades
│   │   ├── ProfileController.php       — Edición de perfil
│   │   ├── AvatarSetupController.php   — Configuración inicial de avatar
│   │   ├── Owner/DashboardController.php — Panel propietario
│   │   └── Admin/AdminController.php   — Panel administrador
│   ├── Models/                         — Eloquent models
│   ├── Services/PricingService.php     — Cálculo de precios (DRY)
│   ├── Support/
│   │   ├── MailHelper.php              — Envío de mail con manejo de errores
│   │   └── PropertyCache.php          — Claves de caché centralizadas
│   ├── Mail/                           — Mailables (notificaciones por email)
│   └── Policies/                       — Autorización (QuintaPolicy, ReservationPolicy)
├── resources/
│   ├── views/                          — Blade templates
│   └── js/
│       ├── app.js                      — Entry point (Alpine.js + sidebar)
│       ├── components/
│       │   ├── phone-input.js          — Componente intl-tel-input
│       │   └── reservas-calendar.js    — Calendario de disponibilidad
│       └── pages/                      — JS específico por página
└── routes/web.php                      — Todas las rutas
```

**Middlewares clave:**
- `auth` — usuario autenticado (Breeze/Sanctum)
- `avatar` — perfil completado (nombre + avatar)
- `role:admin` — solo administradores
- `throttle:120,1` — máx. 120 requests/minuto (webhook MP)

---

## 2. Roles de Usuario

| Rol | Acceso |
|-----|--------|
| **admin** | Todo el sistema. No requiere suscripción. |
| **owner** | Propietario: gestiona sus propiedades y reservas. Necesita suscripción para ver datos de clientes, mensajes y exportar. |
| **client** | Cualquier usuario autenticado. Puede buscar, reservar, favoritar, mensajear, reseñar. |

> Un usuario se convierte en **owner** automáticamente al publicar una propiedad (`isOwner()` detecta si tiene al menos una propiedad).

### Suscripción del Propietario
Sin suscripción, el propietario:
- Ve reservas pero no ve nombre ni contacto del cliente
- No puede leer ni enviar mensajes
- No puede exportar reservas a CSV
- No puede subir facturas a reservas

---

## 3. Módulos del Sistema

### 3.1 Autenticación y Perfil

**Tecnología:** Laravel Breeze (email + password)

**Flujo de registro:**
1. Usuario se registra con nombre, apellido, email, contraseña
2. Se redirige a `/completar-perfil` para subir avatar y completar datos
3. Una vez completado el perfil, accede a todas las funciones

**Perfil editable (`/perfil`):**
- Nombre, apellido, email, teléfono, DNI
- Avatar (imagen de perfil)
- WhatsApp, sitio web
- Redes sociales (Instagram, Facebook, Twitter, TikTok, YouTube)
- Datos bancarios (titular, CBU, alias) — usados por propietarios para recibir transferencias

**Eliminación de cuenta:** soft-delete (`deleted = true`), el usuario no puede volver a iniciar sesión.

---

### 3.2 Propiedades

**Rutas públicas:**
- `GET /` — Inicio: propiedades destacadas + búsqueda rápida
- `GET /propiedades` — Listado con filtros
- `GET /propiedades/{slug}` — Detalle de propiedad

**Filtros de búsqueda disponibles:**
- Provincia, ciudad
- Huéspedes mínimos
- Precio mínimo/máximo
- Amenidades (múltiples)
- Tipo de propiedad
- Habitaciones, baños, estacionamiento
- Calificación mínima
- Horario de entrada/salida disponible
- Fechas (check-in / check-out — excluye propiedades con reservas solapadas)

**Ordenamiento:** Destacadas, precio asc/desc, calificación, más nuevas

**Ciclo de vida de una propiedad:**
```
draft → pending → active → inactive
              ↑_________↑  (el admin puede aprobar/rechazar)
```

**Campos principales:**
- Nombre, descripción, descripción corta
- Dirección completa (calle, número, localidad, partido, provincia)
- Precio por hora, día, semana, mes, fin de semana
- Umbral hora→día (`hour_day_threshold`)
- Capacidad, habitaciones, camas, baños, cocheras
- Amenidades (array JSON)
- Reglas de la casa (array JSON)
- Mín/máx días de estadía
- Horario de entrada/salida disponible
- Descuentos por día de semana, fechas especiales, duración
- Servicios adicionales (vinculados con precios)
- Imágenes (múltiples, con imagen principal)
- Mapa URL (embed externo)

**Amenidades disponibles:** pileta, parrilla, quincho, wifi, estacionamiento, aire acondicionado, calefacción, cancha de fútbol, cancha de tenis, juegos para niños, fogón, jacuzzi, salón de eventos, cocina equipada, lavarropas, smart TV, sistema de sonido, seguridad 24hs

**Caché:** La página de detalle (`/propiedades/{slug}`) se cachea por 1 hora. Se invalida automáticamente al editar, cambiar estado, aprobar/rechazar o cambiar estado de una reserva.

---

### 3.3 Reservas

**Flujo completo del cliente:**

```
1. Cliente selecciona fechas en el calendario de la propiedad
2. POST /propiedades/{slug}/reservar
   → Si no está logueado: se guarda en sesión y redirige al login
   → Si está logueado: se crea la reserva (estado: pending, pago: unpaid)
3. Cliente ve el detalle de la reserva (/mis-reservas/{id})
4. Cliente puede agregar servicios adicionales
5. Cliente inicia el pago → se crea preferencia en MercadoPago
6. MercadoPago redirige al callback → reserva pasa a confirmed + paid
7. Cliente puede dejar reseña una vez completada la reserva
```

**Estados de reserva:**
| Estado | Descripción |
|--------|-------------|
| `pending` | Creada, esperando pago |
| `confirmed` | Pago aprobado |
| `cancelled` | Cancelada por cliente o propietario |
| `completed` | Estadía finalizada |

**Estados de pago:**
| Estado | Descripción |
|--------|-------------|
| `unpaid` | Sin pago |
| `paid` | Pagado vía MercadoPago |
| `refunded` | Reembolsado |

**Campos adicionales:**
- Check-in time / Check-out time (horarios)
- Huéspedes
- Notas del cliente
- Precio desglosado por día (`price_breakdown` JSON)
- Servicios adicionales seleccionados
- Costos extra (agrega el propietario)
- Descuentos (agrega el propietario)
- Factura (PDF/imagen subida por propietario)
- Método de pago
- Tracking de emails enviados (recordatorio check-in, solicitud de reseña)

**Cancelación:** El cliente puede cancelar una reserva que no esté cancelada y cuyo check-in no haya pasado (si ya está confirmada).

---

### 3.4 Pagos (MercadoPago)

**Flujo de pago de reserva:**

```
1. Cliente: POST /mis-reservas/{id}/crear-preferencia
   → Crea preferencia MP con monto total, URLs de retorno, external_reference: "reservation-{id}"
   → Guarda Payment con mp_preference_id

2. MercadoPago procesa el pago

3a. Retorno exitoso: GET /mis-reservas/{id}/pago/exito
    → Llama processPayment() → reservation: confirmed + paid → email al cliente

3b. Retorno fallido: GET /mis-reservas/{id}/pago/fallo
    → Redirige a /pago con mensaje de error

3c. Retorno pendiente: GET /mis-reservas/{id}/pago/pendiente
    → Informa que el pago está en proceso

4. Webhook: POST /webhooks/mercadopago
   → Confirma el pago de forma asíncrona (fuente de verdad)
   → Idempotente: no re-envía email si la reserva ya estaba confirmada+pagada
```

**Seguridad del webhook:**
- Throttle: máx. 120 requests/minuto
- Referencia parseada con regex (`/^reservation-(\d+)$/`) para evitar inyecciones
- Idempotencia contra re-envíos de MP

---

### 3.5 Suscripción de Propietario

**Precio:** Configurable desde el panel admin (por defecto $3.000 ARS, pago único)

**Flujo:**

```
1. Propietario ve banner/alerta en su panel
2. GET /usuario/suscripcion → página de pago
   O GET /usuario/suscripcion/pagar → redirige directo a MercadoPago

3. Se crea preferencia MP con external_reference: "subscription-{user_id}"

4a. Retorno exitoso: GET /usuario/suscripcion/exito
    → activateSubscription() → user.subscription_paid = true → email

4b. Retorno fallido: GET /usuario/suscripcion/fallo
    → Actualiza SubscriptionPayment a rejected

4c. Webhook → processWebhookPayment() → activateSubscription()
    → Idempotente: no re-envía email si ya estaba activo
```

**Columnas en `users`:**
- `subscription_paid` (boolean)
- `subscription_paid_at` (timestamp)

---

### 3.6 Mensajes

**Funcionalidades:**
- Mensajería privada entre cualquier par de usuarios
- Se puede asociar un mensaje a una reserva específica (contexto)
- Contador de mensajes no leídos en el navbar
- Mensajes se marcan como leídos al abrir la conversación

**Restricciones:**
- Propietario **sin suscripción**: no puede leer ni enviar mensajes (redirige a suscripción)
- Al enviar un mensaje se notifica al destinatario por email

**API JSON:** El endpoint `POST /mensajes/{user}` acepta tanto formulario como JSON (para envío AJAX desde la vista de conversación)

---

### 3.7 Reseñas

**Requisitos para dejar reseña:**
1. Usuario autenticado
2. La reserva es del usuario (`user_id`)
3. Estado de la reserva: `confirmed` (completada)
4. La reserva no tiene reseña previa

**Campos:** calificación (1-5 estrellas), comentario (10-1000 caracteres)

**Moderación:** Las reseñas se crean con `approved = false`. El admin las aprueba desde el panel. Al aprobar, se recalcula el rating promedio de la propiedad y se invalida el caché.

---

### 3.8 Favoritos

- Cualquier usuario autenticado puede guardar propiedades como favoritas
- Toggle: un mismo endpoint agrega/quita el favorito
- Si el usuario no está autenticado al intentar guardar un favorito, se redirige al login y se guarda al volver (`/favoritos/{slug}/guardar`)

---

### 3.9 Panel de Propietario

**Acceso:** `/usuario/panel` — cualquier usuario autenticado con al menos una propiedad

**Secciones:**

#### Dashboard (`/usuario/panel`)
- Estadísticas: total propiedades, activas, reservas totales, pendientes, confirmadas, ganancias
- Reservas recientes (últimas 10)
- Lista de sus propiedades con contador de vistas
- Si no tiene suscripción: muestra stats "bloqueadas" para incentivar la compra

#### Propiedades (`/usuario/propiedades`)
- Lista de sus propiedades con estado y acciones
- Puede crear, editar, eliminar (soft-delete), activar/desactivar

#### Reservas (`/usuario/reservas`)
**Requiere suscripción.**
- Listado paginado con filtros (estado, pago, propiedad, fechas)
- Exportar CSV con BOM para Excel
- Ver detalle de reserva
- Cambiar estado (confirmed, cancelled, completed)
- Agregar costos extra y descuentos
- Subir factura (PDF, JPG, PNG — validado por MIME type)
- Descargar PDF de reserva
- Crear reserva manual para clientes sin cuenta

#### Crear reserva manual (`/usuario/reservas/crear`)
- El propietario puede registrar reservas directamente (por teléfono, etc.)
- Selecciona propiedad propia, cliente (usuario existente o datos manuales), fechas, etc.

---

### 3.10 Panel de Administración

**Acceso:** `/admin/panel` — solo usuarios con `role = 'admin'`

#### Dashboard
- Estadísticas globales: usuarios, propiedades, reservas, ingresos, reseñas pendientes
- Propiedades pendientes de aprobación (últimas 10)
- Reservas recientes

#### Propiedades (`/admin/propiedades`)
- Ver todas las propiedades con filtro por estado
- Aprobar (→ `active`) o rechazar (→ `inactive`) propiedades
- Invalida caché al cambiar estado

#### Usuarios (`/admin/usuarios`)
- Lista todos los usuarios con conteo de propiedades y reservas

#### Reseñas (`/admin/resenas`)
- Lista todas las reseñas
- Aprobar (actualiza rating de la propiedad) o eliminar

#### Configuración (`/admin/configuracion`)
- `avatar_required`: si se exige avatar para usar el sistema
- `reviews_enabled`: habilita/deshabilita el sistema de reseñas
- `subscription_price`: precio de la suscripción en ARS

#### Suscripciones (`/admin/suscripciones`)
- Historial de todos los pagos de suscripción
- Estadísticas: total, aprobados, pendientes, rechazados, ingresos

#### Soporte (`/admin/soporte`)
- Tickets de soporte enviados por usuarios
- Cambiar estado: `open`, `in_progress`, `closed`

#### Sugerencias (`/admin/sugerencias`)
- Sugerencias enviadas por usuarios (con adjuntos opcionales)
- Cambiar estado: `pending`, `reviewed`, `done`

---

### 3.11 Soporte y Sugerencias

**Soporte (`/soporte`):**
- Formulario público (no requiere login)
- Campos: nombre, email, asunto, mensaje
- Crea un `SupportTicket` visible en el panel admin

**Sugerencias (`/sugerencias`):**
- Requiere autenticación
- Campos: tipo, título, descripción, adjunto (jpg, jpeg, png, pdf, txt — máx. 5MB)
- El propietario también puede solicitar que le creen un sitio web propio (`/perfil/solicitar-web`)

---

### 3.12 Geo API

**API pública para selectores en cascada:**

- `GET /geo/partidos?province_id={id}` — devuelve partidos de una provincia
- `GET /geo/localidades?partido_id={id}` — devuelve localidades de un partido
- `GET /api/cities?province={nombre}` — devuelve ciudades de una provincia

Usados en los formularios de creación/edición de propiedades para filtrar ubicación en cascada.

---

## 4. Modelos y Base de Datos

### User
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `name`, `last_name` | string | Nombre y apellido |
| `email` | string | Email único |
| `role` | string | `admin` (los demás son owner/client dinámicamente) |
| `phone` | string | Teléfono |
| `dni` | string | DNI |
| `avatar` | string | Ruta o URL del avatar |
| `whatsapp_link` | string | Link de WhatsApp |
| `website` | string | Sitio web personal |
| `social_*` | string | Redes sociales |
| `bank_holder`, `bank_cbu`, `bank_alias` | string | Datos bancarios |
| `subscription_paid` | boolean | Suscripción activa |
| `subscription_paid_at` | timestamp | Fecha de activación |
| `deleted` | boolean | Soft-delete |

**Métodos relevantes:**
- `isAdmin()`, `isOwner()`, `isClient()`
- `hasSubscription()` — verifica `subscription_paid` + pago aprobado en DB
- `needsSubscription()` — `!isAdmin() && !hasSubscription()`
- `unreadMessagesCount()` — mensajes no leídos
- `full_name` (accessor), `avatar_url` (accessor con fallback a ui-avatars)

### Property
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `user_id` | FK | Propietario |
| `name`, `slug` | string | Nombre y URL amigable |
| `description`, `short_description` | text | Descripciones |
| `price_per_hour/day/week/month/weekend` | decimal | Precios |
| `hour_day_threshold` | int | Horas a partir de las cuales aplica precio diario |
| `day_discounts` | JSON | Descuentos por duración `[{days, discount}]` |
| `date_discounts` | JSON | Descuentos por fechas `[{date_from, date_to, discount}]` |
| `weekday_discounts` | JSON | Descuentos por día de semana `[{days[], discount}]` |
| `capacity`, `bedrooms`, `beds`, `bathrooms`, `parking_spots` | int | Capacidades |
| `amenities` | JSON array | Lista de amenidades |
| `rules` | JSON array | Reglas de la casa |
| `status` | string | `draft/pending/active/inactive` |
| `featured` | boolean | Destacada en inicio |
| `min_days`, `max_days` | int | Mín/máx días de reserva |
| `views_count` | int | Contador de vistas |
| `deleted` | boolean | Soft-delete |

**Scopes:** `active()`, `pendingReview()`, `forOwner(User $owner)`

### Reservation
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `property_id`, `user_id` | FK | Propiedad y cliente |
| `check_in`, `check_out` | date | Fechas de estadía |
| `check_in_time`, `check_out_time` | string | Horarios HH:MM |
| `guests` | int | Número de huéspedes |
| `price_per_day`, `total_days` | decimal/int | Precio y duración |
| `subtotal`, `service_fee`, `total_amount` | decimal | Importes |
| `price_breakdown` | JSON | Desglose diario calculado por PricingService |
| `status` | string | `pending/confirmed/cancelled/completed` |
| `payment_status` | string | `unpaid/paid/refunded` |
| `payment_method` | string | Método de pago |
| `notes` | text | Notas del cliente |
| `invoice_path`, `invoice_uploaded_at` | string/timestamp | Factura del propietario |
| `checkin_reminder_sent_at` | timestamp | Tracking de email de recordatorio |
| `review_requested_at` | timestamp | Tracking de solicitud de reseña |

**Scopes:** `pending()`, `confirmed()`, `cancelled()`, `completed()`, `paid()`, `active()`, `forOwner(User $owner)`

### Otros Modelos

| Modelo | Descripción |
|--------|-------------|
| `PropertyImage` | Imágenes de propiedad (path, is_primary, order) |
| `BlockedDate` | Fechas bloqueadas manualmente por el propietario |
| `PropertyService` | Servicios adicionales de la propiedad (nombre, precio) |
| `ReservationService` | Servicios seleccionados en una reserva (quantity, price) |
| `ReservationExtraCost` | Costos extra agregados por el propietario |
| `ReservationDiscount` | Descuentos agregados por el propietario |
| `Payment` | Registro de pago MP (mp_preference_id, mp_payment_id, status, paid_at) |
| `Review` | Reseña (rating, comment, approved) |
| `Message` | Mensaje (sender_id, receiver_id, reservation_id, body, read_at) |
| `SubscriptionPayment` | Registro de pago de suscripción |
| `SupportTicket` | Ticket de soporte (status: open/in_progress/closed) |
| `Suggestion` | Sugerencia de usuario (status: pending/reviewed/done) |
| `Province`, `City` | Provincias y ciudades (con orden y active) |
| `Partido`, `Localidad` | Partidos y localidades para geolocalización |
| `Setting` | Configuraciones clave-valor del sistema |
| `PropertyType` | Tipos de propiedad |

---

## 5. Sistema de Precios

Toda la lógica de cálculo está en `app/Services/PricingService.php`.

### Modalidades de precio

**Por hora** (mismo día entrada y salida):
```
total = precio_por_hora × horas
```

**Por día** (distinto día entrada y salida):
```
Para cada día se aplica el mejor descuento disponible:
  1. Descuento por día de semana (weekday_discounts)
  2. Descuento por fecha especial (date_discounts)
  → Se toma el mayor de los dos

Luego, si la duración total supera un umbral, se aplica
el descuento por duración (day_discounts):
  → Se toma el tramo más alto alcanzado
```

### Ejemplo de price_breakdown (por día)
```json
{
  "type": "daily",
  "days": [
    {"date": "15/04/2026", "weekday": "Mié", "base_price": 10000, "discount_pct": 10, "discount_reason": "Día especial", "price": 9000},
    {"date": "16/04/2026", "weekday": "Jue", "base_price": 10000, "discount_pct": 0, "discount_reason": null, "price": 10000}
  ],
  "duration_discount_pct": 5,
  "subtotal": 18050
}
```

---

## 6. Sistema de Caché

**Driver:** configurado en `.env` (`CACHE_DRIVER`, por defecto `file`)

| Clave | TTL | Contenido | Se invalida cuando |
|-------|-----|-----------|-------------------|
| `home` | 30 min | Propiedades destacadas, tipos, provincias | Nueva propiedad, edición, cambio estado, aprobación |
| `properties.index` | — | (reservado) | Igual que home |
| `property.show.{slug}` | 1 hora | Datos de propiedad, fechas no disponibles, similares | Edición, toggle estado, aprobación, cambio estado reserva |

**Clase helper:** `app/Support/PropertyCache.php`
- `PropertyCache::forShow($slug)` — devuelve la clave
- `PropertyCache::clear($propiedad)` — limpia show + home
- `PropertyCache::clearListings()` — limpia home

**Lo que NO se cachea:**
- `reservaParaReseña` en el show (depende del usuario autenticado)
- Listado de propiedades con filtros (parámetros dinámicos)
- Cualquier dato específico del usuario

---

## 7. Emails del Sistema

Todos los emails usan `MailHelper::send()` que captura errores y los loguea sin romper el flujo.

| Evento | Destinatario | Mailable |
|--------|-------------|----------|
| Nueva reserva creada | Propietario | `NewReservationNotification` |
| Reserva confirmada (pago aprobado) | Cliente | `ReservationConfirmedNotification` |
| Reserva cancelada por cliente | Propietario | `ReservationCancelledOwnerNotification` |
| Reserva cancelada por propietario | Cliente | `ReservationCancelledClientNotification` |
| Factura subida | Cliente | `InvoiceUploadedNotification` |
| Nueva reseña | Propietario | `NewReviewNotification` |
| Nuevo mensaje | Destinatario | `NewMessageNotification` |
| Suscripción activada | Propietario | `SubscriptionActivatedNotification` |

**Protección contra duplicados (idempotencia):**
- Confirmación de reserva: se verifica `isConfirmed() && isPaid()` antes de enviar
- Activación de suscripción: se verifica `subscription_paid === true` antes de enviar

---

## 8. Rutas Completas

### Públicas
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/` | Página de inicio |
| GET | `/propiedades` | Listado de propiedades |
| GET | `/propiedades/{slug}` | Detalle de propiedad |
| GET | `/api/cities` | Ciudades por provincia |
| GET | `/geo/partidos` | Partidos por province_id |
| GET | `/geo/localidades` | Localidades por partido_id |
| POST | `/propiedades/{slug}/reservar` | Crear reserva (redirige a login si no auth) |
| POST | `/webhooks/mercadopago` | Webhook de MercadoPago |
| GET | `/soporte` | Formulario de soporte |
| POST | `/soporte` | Enviar ticket de soporte |
| GET | `/favoritos/{slug}/guardar` | Guardar favorito post-login |

### Autenticadas (auth + avatar)
| Método | URL | Descripción |
|--------|-----|-------------|
| GET/PATCH | `/perfil` | Ver/editar perfil |
| DELETE | `/perfil` | Eliminar cuenta |
| GET | `/mis-reservas` | Mis reservas |
| GET | `/mis-reservas/{id}` | Detalle de reserva |
| GET | `/mis-reservas/{id}/pago` | Página de pago |
| POST | `/mis-reservas/{id}/cancelar` | Cancelar reserva |
| PATCH | `/mis-reservas/{id}/servicios` | Actualizar servicios |
| POST | `/mis-reservas/{id}/crear-preferencia` | Iniciar pago MP |
| GET | `/mis-reservas/{id}/pago/exito` | Callback éxito MP |
| GET | `/mis-reservas/{id}/pago/fallo` | Callback fallo MP |
| GET | `/mis-reservas/{id}/pago/pendiente` | Callback pendiente MP |
| POST | `/mis-reservas/{id}/resena` | Dejar reseña |
| GET | `/favoritos` | Ver favoritos |
| POST | `/favoritos/{slug}` | Toggle favorito |
| GET | `/mensajes` | Bandeja de mensajes |
| GET | `/mensajes/{user}` | Conversación con usuario |
| POST | `/mensajes/{user}` | Enviar mensaje |
| GET/POST | `/sugerencias` | Ver/enviar sugerencia |
| GET | `/completar-perfil` | Setup inicial de avatar |
| POST | `/completar-perfil` | Guardar avatar inicial |

### Suscripción (`/usuario/suscripcion*`)
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/usuario/suscripcion` | Página de pago |
| GET | `/usuario/suscripcion/pagar` | Redirige a MP |
| POST | `/usuario/suscripcion/crear-preferencia` | Crear preferencia |
| GET | `/usuario/suscripcion/exito` | Callback éxito |
| GET | `/usuario/suscripcion/fallo` | Callback fallo |
| GET | `/usuario/suscripcion/pendiente` | Callback pendiente |

### Panel Propietario (`/usuario/*`)
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/usuario/panel` | Dashboard |
| GET | `/usuario/propiedades` | Mis propiedades |
| GET | `/usuario/propiedades/crear` | Form crear propiedad |
| POST | `/usuario/propiedades` | Guardar propiedad |
| GET | `/usuario/propiedades/{id}/editar` | Form editar |
| PUT | `/usuario/propiedades/{id}` | Actualizar propiedad |
| DELETE | `/usuario/propiedades/{id}` | Eliminar propiedad |
| PATCH | `/usuario/propiedades/{id}/toggle` | Activar/desactivar |
| DELETE | `/usuario/imagenes/{id}` | Eliminar imagen |
| GET | `/usuario/reservas` | Mis reservas recibidas |
| GET | `/usuario/reservas/exportar` | Exportar CSV |
| GET | `/usuario/reservas/crear` | Crear reserva manual |
| POST | `/usuario/reservas` | Guardar reserva manual |
| GET | `/usuario/reservas/{id}` | Ver reserva |
| PATCH | `/usuario/reservas/{id}/estado` | Cambiar estado |
| GET | `/usuario/reservas/{id}/pdf` | Descargar PDF |
| POST | `/usuario/reservas/{id}/factura` | Subir factura |
| DELETE | `/usuario/reservas/{id}/factura` | Eliminar factura |

### Panel Admin (`/admin/*`)
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/admin/panel` | Dashboard |
| GET | `/admin/propiedades` | Todas las propiedades |
| PATCH | `/admin/propiedades/{id}/aprobar` | Aprobar propiedad |
| PATCH | `/admin/propiedades/{id}/rechazar` | Rechazar propiedad |
| GET | `/admin/usuarios` | Todos los usuarios |
| GET | `/admin/resenas` | Todas las reseñas |
| PATCH | `/admin/resenas/{id}/aprobar` | Aprobar reseña |
| DELETE | `/admin/resenas/{id}` | Eliminar reseña |
| GET/POST | `/admin/configuracion` | Configuración del sistema |
| GET | `/admin/suscripciones` | Pagos de suscripción |
| GET | `/admin/soporte` | Tickets de soporte |
| PATCH | `/admin/soporte/{id}/status` | Cambiar estado ticket |
| GET | `/admin/sugerencias` | Sugerencias |
| PATCH | `/admin/sugerencias/{id}/status` | Cambiar estado sugerencia |

---

## 9. Guía de Testing Manual

### Configuración previa
1. Configurar `.env` con DB, MercadoPago (credenciales de sandbox), MAIL
2. `php artisan migrate --seed`
3. `php artisan storage:link`
4. `npm run build` o `npm run dev`

---

### BLOQUE 1 — Registro y Perfil

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 1.1 | Registrarse con email nuevo | Redirige a `/completar-perfil` |
| 1.2 | Acceder a `/propiedades` sin completar perfil | Redirige a `/completar-perfil` |
| 1.3 | Completar perfil (nombre + avatar) | Redirige al panel/inicio |
| 1.4 | Editar perfil: cambiar teléfono, redes sociales | Cambios guardados correctamente |
| 1.5 | Subir avatar inválido (PDF) | Error de validación |
| 1.6 | Eliminar cuenta | Usuario eliminado (soft-delete), no puede volver a iniciar sesión |
| 1.7 | Intentar login con cuenta eliminada | Error de autenticación |

---

### BLOQUE 2 — Búsqueda de Propiedades (Público)

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 2.1 | Abrir `/propiedades` sin filtros | Lista propiedades activas |
| 2.2 | Filtrar por provincia | Solo muestra propiedades de esa provincia |
| 2.3 | Filtrar por huéspedes = 10 | Solo propiedades con capacidad ≥ 10 |
| 2.4 | Filtrar por precio máximo | Solo propiedades ≤ precio indicado |
| 2.5 | Filtrar por amenidad "pileta" | Solo propiedades con pileta |
| 2.6 | Filtrar por fechas ocupadas | No muestra propiedades con reservas confirmadas en esas fechas |
| 2.7 | Ordenar por precio ascendente | Lista ordenada correctamente |
| 2.8 | Abrir detalle de propiedad activa | Muestra datos, imágenes, calendario, servicios |
| 2.9 | Intentar acceder a propiedad inactiva | Devuelve 404 |
| 2.10 | Verificar que el caché funciona | Segunda visita al detalle no genera queries DB (verificar en logs) |

---

### BLOQUE 3 — Reservas (Cliente)

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 3.1 | Intentar reservar sin login | Redirige a login, al volver completa la reserva |
| 3.2 | Reservar con fechas válidas | Reserva creada en estado `pending`, propietario recibe email |
| 3.3 | Reservar con fecha de check-in en el pasado | Error de validación |
| 3.4 | Reservar con check-out antes del check-in | Error de validación |
| 3.5 | Reservar en propiedad con mínimo de días (ej. 2) y poner 1 día | Error "estadía mínima" |
| 3.6 | Reservar en fechas ya ocupadas | Error "fechas no disponibles" |
| 3.7 | Ver detalle de reserva | Muestra desglose de precios, servicios disponibles |
| 3.8 | Agregar servicios adicionales a la reserva | Subtotal se actualiza |
| 3.9 | Intentar ver reserva de otro usuario | Error 403 |
| 3.10 | Cancelar reserva pendiente | Estado cambia a `cancelled`, propietario recibe email |
| 3.11 | Intentar cancelar reserva ya cancelada | Error 403 |
| 3.12 | Ver `/mis-reservas` | Lista todas las reservas del usuario paginadas |

---

### BLOQUE 4 — Pagos MercadoPago

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 4.1 | Iniciar pago en reserva pendiente | Redirige a MercadoPago sandbox |
| 4.2 | Pagar exitosamente (sandbox) | Reserva → `confirmed` + `paid`, cliente recibe email |
| 4.3 | Pago rechazado (sandbox) | Redirige a página de pago con error |
| 4.4 | Pago pendiente (ej. transferencia) | Mensaje "pago en proceso" |
| 4.5 | Intentar iniciar pago en reserva ya pagada | Error 400 |
| 4.6 | Simular re-envío de webhook con mismo payment_id | No se re-envía el email de confirmación |
| 4.7 | Verificar log del webhook | Aparece entrada en `storage/logs/laravel.log` |

---

### BLOQUE 5 — Suscripción de Propietario

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 5.1 | Propietario sin suscripción accede a `/usuario/reservas` | Redirige a suscripción |
| 5.2 | Propietario sin suscripción intenta enviar mensaje | Error 403 / redirige |
| 5.3 | Acceder a `/usuario/suscripcion` | Muestra página de pago con precio |
| 5.4 | Pagar suscripción (sandbox) | `subscription_paid = true`, propietario recibe email |
| 5.5 | Propietario con suscripción accede a reservas | Ve datos completos del cliente |
| 5.6 | Admin cambia precio de suscripción | Nueva suscripción usa precio actualizado |
| 5.7 | Intentar pagar suscripción ya activa | Redirige al panel con mensaje "ya tenés suscripción" |

---

### BLOQUE 6 — Gestión de Propiedades (Propietario)

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 6.1 | Crear propiedad con datos válidos | Propiedad en estado `pending` |
| 6.2 | Crear propiedad sin nombre | Error de validación |
| 6.3 | Subir imagen WebP | Aceptada |
| 6.4 | Subir imagen GIF | Rechazada (mimes: jpeg,jpg,png,webp) |
| 6.5 | Editar propiedad propia | Cambios guardados, caché invalidado |
| 6.6 | Intentar editar propiedad de otro propietario | Error 403 |
| 6.7 | Desactivar propiedad | Estado cambia, no aparece en búsqueda pública |
| 6.8 | Eliminar propiedad | Soft-delete, desaparece de la lista |
| 6.9 | Agregar servicio adicional a propiedad | Aparece disponible al reservar |
| 6.10 | Configurar descuento por 3 días | Al reservar 3+ días se aplica el descuento |
| 6.11 | Configurar descuento día miércoles | El miércoles muestra precio reducido en el desglose |

---

### BLOQUE 7 — Panel del Propietario (Reservas)

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 7.1 | Ver lista de reservas recibidas | Solo ve reservas de sus propiedades |
| 7.2 | Filtrar por estado "pending" | Solo reservas pendientes |
| 7.3 | Exportar reservas a CSV | Descarga archivo con BOM para Excel |
| 7.4 | Confirmar una reserva manualmente | Estado → `confirmed`, cliente recibe email |
| 7.5 | Cancelar reserva como propietario | Estado → `cancelled`, cliente recibe email |
| 7.6 | Subir factura PDF válida | Se guarda, cliente recibe email con link |
| 7.7 | Subir factura con extensión .exe | Rechazada por validación MIME |
| 7.8 | Descargar PDF de reserva | PDF generado correctamente |
| 7.9 | Crear reserva manual para un cliente | Reserva creada correctamente |
| 7.10 | Agregar costo extra a reserva | Total de reserva se actualiza |
| 7.11 | Agregar descuento a reserva | Total de reserva se actualiza |

---

### BLOQUE 8 — Mensajes

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 8.1 | Cliente envía mensaje al propietario | Mensaje guardado, propietario recibe email |
| 8.2 | Propietario con suscripción responde | Mensaje enviado correctamente |
| 8.3 | Propietario sin suscripción abre mensajes | Redirige a página de suscripción |
| 8.4 | Propietario sin suscripción intenta enviar | Error 403 |
| 8.5 | Abrir conversación con mensajes no leídos | Mensajes marcados como leídos |
| 8.6 | Verificar contador en navbar | Decrece al leer mensajes |

---

### BLOQUE 9 — Reseñas

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 9.1 | Dejar reseña en reserva completada propia | Reseña creada (`approved = false`), propietario recibe email |
| 9.2 | Intentar dejar reseña en reserva pendiente | Error 403 |
| 9.3 | Intentar reseñar una reserva que ya tiene reseña | Error 403 |
| 9.4 | Admin aprueba la reseña | Aparece en el detalle de propiedad, rating actualizado |
| 9.5 | Admin elimina reseña | Desaparece, rating NO se recalcula (verificar) |
| 9.6 | Verificar rating promedio en propiedad | Coincide con promedio de reseñas aprobadas |

---

### BLOQUE 10 — Panel Admin

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 10.1 | Aprobar propiedad pendiente | Estado → `active`, aparece en búsqueda pública |
| 10.2 | Rechazar propiedad | Estado → `inactive`, no aparece en búsqueda |
| 10.3 | Ver usuarios | Lista con conteo de propiedades y reservas |
| 10.4 | Cambiar precio de suscripción a $5000 | Nueva suscripción muestra $5000 |
| 10.5 | Desactivar avatar requerido | Usuarios sin avatar pueden acceder a todas las rutas |
| 10.6 | Ver pagos de suscripción | Historial completo con estadísticas |
| 10.7 | Cambiar estado de ticket de soporte a "closed" | Estado actualizado |
| 10.8 | Intentar acceder a `/admin/panel` como cliente | Error 403 |

---

### BLOQUE 11 — Favoritos

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 11.1 | Agregar propiedad a favoritos | Ícono cambia, propiedad en `/favoritos` |
| 11.2 | Quitar propiedad de favoritos | Ícono vuelve, desaparece de `/favoritos` |
| 11.3 | Click en favorito sin estar logueado | Redirige a login, al volver guarda el favorito |

---

### BLOQUE 12 — Soporte y Sugerencias

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 12.1 | Enviar ticket de soporte (sin login) | Ticket creado, visible en panel admin |
| 12.2 | Enviar sugerencia con adjunto PDF | Guardada correctamente |
| 12.3 | Enviar sugerencia con adjunto .zip | Rechazada por validación |
| 12.4 | Admin cambia estado de sugerencia a "done" | Estado actualizado en lista |

---

### BLOQUE 13 — Seguridad

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 13.1 | Intentar ver reserva de otro usuario por URL | Error 403 |
| 13.2 | Enviar `property_service_id` de otra propiedad al actualizar servicios | ID ignorado silenciosamente |
| 13.3 | Enviar múltiples requests rápidos al webhook | Throttle activo (max 120/min) |
| 13.4 | Inyectar SQL en filtros de búsqueda | Consultas parametrizadas, no hay efecto |
| 13.5 | Intentar subir archivo .php como imagen | Rechazado por validación MIME |
| 13.6 | Intentar acceder a `/admin/*` como propietario | Error 403 |
| 13.7 | Intentar enviar `subscription-999` en external_reference falso | Solo procesa IDs existentes en DB |

---

*Documentación generada el 2026-04-10*
