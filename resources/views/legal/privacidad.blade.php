@extends('layouts.main')

@section('title', 'Política de Privacidad — ReservaTuEspacio')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Política de Privacidad</h1>
    <p class="text-sm text-gray-400 mb-10">Última actualización: {{ date('d/m/Y') }}</p>

    <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">1. Información que recopilamos</h2>
            <p>Cuando te registrás en ReservaTuEspacio recopilamos los siguientes datos personales:</p>
            <ul class="list-disc pl-6 mt-2 space-y-1">
                <li>Nombre y apellido</li>
                <li>Dirección de correo electrónico</li>
                <li>Número de teléfono (opcional)</li>
                <li>Foto de perfil (cuando usás login con Google o Facebook)</li>
                <li>Información de reservas realizadas</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">2. Cómo usamos tu información</h2>
            <p>Usamos tus datos para:</p>
            <ul class="list-disc pl-6 mt-2 space-y-1">
                <li>Gestionar tu cuenta y las reservas que realizás</li>
                <li>Comunicarnos con vos sobre tus reservas o consultas</li>
                <li>Mejorar la experiencia en la plataforma</li>
                <li>Enviarte notificaciones relevantes sobre tus reservas</li>
            </ul>
            <p class="mt-3">No vendemos ni compartimos tus datos personales con terceros con fines comerciales.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">3. Login con redes sociales</h2>
            <p>Si elegís registrarte con Google o Facebook, recibimos únicamente tu nombre, email y foto de perfil públicos. No accedemos a tu contraseña ni a ningún otro dato de tu cuenta en dichas plataformas.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">4. Cookies</h2>
            <p>Usamos cookies de sesión para mantener tu inicio de sesión activo. No utilizamos cookies de seguimiento ni publicidad de terceros.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">5. Seguridad</h2>
            <p>Tus datos se almacenan de forma segura. Las contraseñas se guardan encriptadas y nunca en texto plano. Utilizamos HTTPS en toda la plataforma.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">6. Eliminación de datos</h2>
            <p>Podés solicitar la eliminación de tu cuenta y todos tus datos personales en cualquier momento escribiéndonos a <a href="mailto:{{ config('mail.from.address', 'contacto@reservatuespacio.com.ar') }}" class="text-indigo-600 hover:underline">contacto@reservatuespacio.com.ar</a>. Procesaremos tu solicitud dentro de los 30 días hábiles.</p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">7. Contacto</h2>
            <p>Si tenés alguna consulta sobre esta política de privacidad podés escribirnos a <a href="mailto:{{ config('mail.from.address', 'contacto@reservatuespacio.com.ar') }}" class="text-indigo-600 hover:underline">contacto@reservatuespacio.com.ar</a>.</p>
        </section>

    </div>
</div>
@endsection
