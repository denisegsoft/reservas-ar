<?php
/**
 * WhatsApp Business API - Webhook Bot
 * Reserva Tu Espacio - reservatuespacio.com.ar
 */

// ============================================================
// CONFIGURACIÓN
// ============================================================
define('WHATSAPP_TOKEN',    'EAHwaaJ1IDD0BRfSKr7JCjWof9gqyF906BpL5FQZCLOW3mMMELyWdHhLfAZCZB3hSPQUZCFOhgC5er14Az4EZAPmSxiK26aW2pGYZCXrVKdkJ7MLhZCH8NOYGFlbiog89uQNX28WLEZBXcjghgm6EuGZBZCEOXoJcur9CsJO4nTRrgAiGG3O1Hjteuz1ohDUOZA69tyMZAAvhAwkbzHiUzZBPsgHFv1ZBQdkKZBFL2ZB7m3wlZAKOW0czzfFLuZBQmk1GSRoOVd2KnPQzzxCSRLny4F4NUkEDQP');
define('PHONE_NUMBER_ID',   '1086208644574371');
define('VERIFY_TOKEN',      'reservatuespacio_webhook_2024'); // Lo usás al configurar el webhook en Meta

// ============================================================
// VERIFICACIÓN DEL WEBHOOK (Meta lo llama una vez al configurar)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode      = $_GET['hub_mode']        ?? '';
    $token     = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge']    ?? '';

    if ($mode === 'subscribe' && $token === VERIFY_TOKEN) {
        http_response_code(200);
        echo $challenge;
    } else {
        http_response_code(403);
        echo 'Token inválido';
    }
    exit;
}

// ============================================================
// RECEPCIÓN DE MENSAJES (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    // Extraer mensaje entrante
    $entry   = $data['entry'][0]          ?? null;
    $changes = $entry['changes'][0]        ?? null;
    $value   = $changes['value']           ?? null;
    $message = $value['messages'][0]       ?? null;

    if ($message && $message['type'] === 'text') {
        $from = $message['from'];                         // número del usuario
        $text = strtolower(trim($message['text']['body'])); // texto recibido

        $respuesta = obtenerRespuesta($text);
        enviarMensaje($from, $respuesta);
    }

    http_response_code(200);
    echo 'OK';
    exit;
}

// ============================================================
// RESPUESTAS AUTOMÁTICAS
// ============================================================
function obtenerRespuesta(string $texto): string
{
    // Menú principal
    $menu = "👋 ¡Hola! Soy el asistente de *Reserva Tu Espacio*.\n\n"
          . "¿En qué te puedo ayudar?\n\n"
          . "1️⃣ *precios* - Ver tarifas\n"
          . "2️⃣ *disponibilidad* - Consultar fechas\n"
          . "3️⃣ *reservar* - Cómo hacer una reserva\n"
          . "4️⃣ *ubicacion* - Dónde estamos\n"
          . "5️⃣ *contacto* - Hablar con una persona\n\n"
          . "Escribí la palabra clave o el número de opción.";

    // Palabras clave → respuesta
    $respuestas = [
        // Saludo
        'hola'          => $menu,
        'buenas'        => $menu,
        'buenos dias'   => $menu,
        'buenas tardes' => $menu,
        'buenas noches' => $menu,
        'menu'          => $menu,
        '0'             => $menu,

        // Precios
        'precios'       => "💰 *Tarifas de nuestras quintas:*\n\n"
                         . "• Turno día (10hs a 18hs): desde $80.000\n"
                         . "• Turno noche (20hs a 04hs): desde $100.000\n"
                         . "• Día completo: desde $150.000\n\n"
                         . "Los precios varían según la quinta y la temporada.\n"
                         . "Para ver todas las opciones visitá: https://reservatuespacio.com.ar",
        '1'             => "💰 *Tarifas de nuestras quintas:*\n\n"
                         . "• Turno día (10hs a 18hs): desde $80.000\n"
                         . "• Turno noche (20hs a 04hs): desde $100.000\n"
                         . "• Día completo: desde $150.000\n\n"
                         . "Visitá https://reservatuespacio.com.ar para ver todas las quintas.",

        // Disponibilidad
        'disponibilidad' => "📅 *Consultar disponibilidad:*\n\n"
                          . "Para ver las fechas disponibles de cada quinta, ingresá a nuestra web y seleccioná la quinta que te interesa:\n\n"
                          . "🌐 https://reservatuespacio.com.ar\n\n"
                          . "También podés escribirnos la fecha y la quinta que te interesa y te confirmamos.",
        '2'              => "📅 *Consultar disponibilidad:*\n\n"
                          . "Ingresá a https://reservatuespacio.com.ar, elegí la quinta y consultá el calendario de disponibilidad.",

        // Reservar
        'reservar'      => "✅ *¿Cómo reservar?*\n\n"
                         . "1. Entrá a https://reservatuespacio.com.ar\n"
                         . "2. Elegí la quinta que te gusta\n"
                         . "3. Seleccioná la fecha y el turno\n"
                         . "4. Completá tus datos y pagá online\n\n"
                         . "¡En minutos tenés tu reserva confirmada! 🎉",
        'como reservo'  => "✅ Podés reservar fácilmente desde nuestra web:\n🌐 https://reservatuespacio.com.ar",
        '3'             => "✅ *¿Cómo reservar?*\n\n"
                         . "Ingresá a https://reservatuespacio.com.ar, elegí tu quinta y seguí los pasos para reservar online.",

        // Ubicación
        'ubicacion'     => "📍 *Nuestras quintas están ubicadas en la provincia de Buenos Aires.*\n\n"
                         . "Cada quinta tiene su dirección detallada en la web:\n"
                         . "🌐 https://reservatuespacio.com.ar\n\n"
                         . "Podés filtrar por zona o ciudad.",
        'donde estan'   => "📍 Tenemos quintas en toda la provincia de Buenos Aires. Consultá ubicaciones en https://reservatuespacio.com.ar",
        '4'             => "📍 Tenemos quintas en toda la provincia de Buenos Aires. Consultá ubicaciones en https://reservatuespacio.com.ar",

        // Contacto
        'contacto'      => "👤 *Contacto directo:*\n\n"
                         . "Si necesitás hablar con una persona de nuestro equipo, podés:\n\n"
                         . "📧 Email: info@reservatuespacio.com.ar\n"
                         . "🌐 Web: https://reservatuespacio.com.ar\n\n"
                         . "Respondemos en horario comercial (Lun-Vie 9 a 18hs).",
        '5'             => "👤 Para hablar con una persona escribinos a info@reservatuespacio.com.ar o contactanos desde la web.",

        // Gracias / Despedida
        'gracias'       => "¡De nada! 😊 Si necesitás algo más, escribinos. ¡Que tengas un excelente día!",
        'ok'            => "¡Perfecto! 👍 Si necesitás algo más, escribí *menú* para ver las opciones.",
        'chau'          => "¡Hasta luego! 👋 Cuando quieras reservar tu espacio, acá estamos.",
    ];

    // Búsqueda exacta primero
    if (isset($respuestas[$texto])) {
        return $respuestas[$texto];
    }

    // Búsqueda por palabras clave parciales
    foreach ($respuestas as $clave => $respuesta) {
        if (str_contains($texto, $clave)) {
            return $respuesta;
        }
    }

    // Respuesta por defecto
    return "🤖 No entendí tu consulta. Escribí *menú* para ver las opciones disponibles, o visitanos en https://reservatuespacio.com.ar";
}

// ============================================================
// ENVIAR MENSAJE VÍA API
// ============================================================
function enviarMensaje(string $to, string $texto): void
{
    $url  = 'https://graph.facebook.com/v20.0/' . PHONE_NUMBER_ID . '/messages';
    $body = json_encode([
        'messaging_product' => 'whatsapp',
        'to'                => $to,
        'type'              => 'text',
        'text'              => ['body' => $texto],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . WHATSAPP_TOKEN,
            'Content-Type: application/json',
        ],
    ]);
    curl_exec($ch);
    curl_close($ch);
}
