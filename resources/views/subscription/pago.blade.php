@extends('layouts.main')
@section('title', 'Activá tu suscripción')
@section('minimal_layout', true)
@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 text-sm px-5 py-3.5 rounded-2xl flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>{{ session('success') }}
            @if(session('success_property_slug'))
                <a href="{{ route('properties.show', session('success_property_slug')) }}" target="_blank" class="ml-2 font-semibold underline hover:text-green-900 whitespace-nowrap">Ver propiedad</a>
            @endif
        </span>
    </div>
    @endif

    @if(session('error') || !empty($mpError))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-5 py-3.5 rounded-2xl">
        <p class="font-semibold mb-1">No se pudo conectar con MercadoPago</p>
        <p class="text-xs opacity-75">{{ session('error') ?? $mpError }}</p>
        <a href="{{ route('subscription.payment') }}" class="inline-block mt-2 text-xs font-semibold underline">Reintentar</a>
    </div>
    @endif

    {{-- Bienvenida --}}
    <div class="mb-6 text-center">
        <p class="text-gray-400 text-sm uppercase tracking-widest font-medium mb-1">Bienvenido</p>
        <h1 class="text-2xl font-black text-gray-900">{{ auth()->user()->name }}</h1>
    </div>

    {{-- Hero promo card --}}
    <div class="relative overflow-hidden rounded-3xl mb-6 shadow-2xl"
         style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);">
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/10 rounded-full"></div>

        <div class="relative p-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">🏆</div>
                <div>
                    <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wider">¡Tu propiedad ya está lista!</p>
                    <h2 class="text-white text-2xl font-black leading-tight">Activá tu cuenta de propietario</h2>
                </div>
            </div>

            <p class="text-indigo-100 text-sm leading-relaxed mb-6">
                Con un <strong class="text-white">único pago</strong>, desbloqueás todo lo que necesitás para gestionar
                tus reservas y conectar con tus clientes. Sin renovaciones.
            </p>

            {{-- Benefits --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-7">
                <div class="sm:col-span-2 bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl mt-0.5">💰</span>
                    <div>
                        <p class="text-white font-semibold text-sm">Concretá tus ventas</p>
                        <p class="text-indigo-200 text-xs mt-0.5">Contacto directo con tus clientes y posibles clientes, marketing, posicionamiento, asesoría de ventas</p>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl mt-0.5">📞</span>
                    <div>
                        <p class="text-white font-semibold text-sm">Recibí reservas</p>
                        <p class="text-indigo-200 text-xs mt-0.5">Los clientes se comunican directamente con vos</p>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl mt-0.5">📈</span>
                    <div>
                        <p class="text-white font-semibold text-sm">Panel completo</p>
                        <p class="text-indigo-200 text-xs mt-0.5">Gestioná propiedades, reservas, clientes y ganancias</p>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl mt-0.5">🤖</span>
                    <div>
                        <p class="text-white font-semibold text-sm">Chatbot inteligente para tu WhatsApp Business</p>
                        <p class="text-indigo-200 text-xs mt-0.5">Ahorra tu tiempo y automatizá las respuestas de tu empresa en WhatsApp Business y atendé a tus clientes las 24 hs</p>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl mt-0.5">🌐</span>
                    <div>
                        <p class="text-white font-semibold text-sm">Sitio web profesional</p>
                        <p class="text-indigo-200 text-xs mt-0.5">Creamos tu página web a medida para mostrar tu negocio al mundo</p>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl mt-0.5">📱</span>
                    <div>
                        <p class="text-white font-semibold text-sm">Gestión de redes y publicaciones</p>
                        <p class="text-indigo-200 text-xs mt-0.5">Gestioná todas tus redes sociales y publicaciones desde el panel</p>
                    </div>
                </div>
            </div>

            {{-- Price + Pay button --}}
            <div class="bg-white rounded-3xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-gray-400 text-xs font-medium">Pago único · Sin renovaciones</p>
                        @if($discount)
                        <p class="text-red-400 text-xl font-bold leading-tight" style="text-decoration: line-through;">${{ number_format($basePrice, 0, ',', '.') }} <span class="text-base font-semibold">ARS</span></p>
                        <p class="text-gray-900 text-3xl font-black leading-tight">${{ number_format($price, 0, ',', '.') }} <span class="text-lg font-semibold text-gray-400">ARS</span></p>
                        <span class="inline-block mt-1 text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">🏷️ {{ $discount['pct'] }}% OFF{{ $discount['label'] ? ' · ' . $discount['label'] : '' }}</span>
                        @else
                        <p class="text-gray-900 text-3xl font-black">${{ number_format($price, 0, ',', '.') }} <span class="text-lg font-semibold text-gray-400">ARS</span></p>
                        @endif
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>

                @if($initPoint)
                <a href="{{ $initPoint }}"
                   class="w-full flex items-center justify-center gap-3 text-white font-bold py-4 rounded-2xl transition-all text-base shadow-sm"
                   style="background-color:#009ee3;"
                   onmouseover="this.style.backgroundColor='#0088cc'"
                   onmouseout="this.style.backgroundColor='#009ee3'">
                    <img src="{{ asset('images/mercadopago-logo.png') }}" alt="MercadoPago" width="70">
                    Pagar ${{ number_format($price, 0, ',', '.') }} ARS
                </a>
                @else
                <p class="text-center text-sm text-gray-500 py-4">
                    No se pudo generar el enlace de pago.
                    <a href="{{ route('subscription.payment') }}" class="underline font-medium">Reintentar</a>
                </p>
                @endif

                <div class="flex items-center gap-3 mt-5 pt-5 border-t border-gray-100">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-gray-400">Pago 100% seguro procesado por MercadoPago. Tus datos financieros nunca son compartidos con nosotros.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sin suscripción --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <span class="text-xl flex-shrink-0">⚠️</span>
            <div>
                <p class="text-amber-800 font-semibold text-sm mb-1">Sin suscripción activa:</p>
                <ul class="text-amber-700 text-sm space-y-1">
                    <li>• Los clientes no ven tu información de contacto</li>
                    <li>• No podés ver quién solicitó una reserva</li>
                    <li>• Acceso limitado a la plataforma y servicios adicionales</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection
