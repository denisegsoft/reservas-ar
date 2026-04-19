@extends('layouts.main')
@section('title', 'Inicio')
@section('description', 'Reservá quintas, salones y espacios para eventos en Argentina. Más de 500 propiedades verificadas. Encontrá el lugar ideal para tu cumpleaños, casamiento o reunión de empresa.')
@section('keywords', 'reserva quintas Argentina, alquiler salones eventos, quintas Buenos Aires, espacios para eventos, reservar quinta online, salones de fiesta')

@push('head')
@php
$_websiteSchema = json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'WebSite',
    'name'            => 'ReservaTuEspacio',
    'url'             => config('app.url'),
    'description'     => 'Encontrá y reservá quintas, salones y espacios para eventos en Argentina. Más de 500 propiedades verificadas.',
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => route('properties.index') . '?state={state}&guests={guests}'],
        'query-input' => 'required name=state',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$_orgSchema = json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'Organization',
    'name'        => 'ReservaTuEspacio',
    'url'         => config('app.url'),
    'description' => 'Sistema exclusivo para reservar quintas, salones y espacios para eventos en Argentina.',
    'areaServed'  => ['@type' => 'Country', 'name' => 'Argentina'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<script type="application/ld+json">{!! $_websiteSchema !!}</script>
<script type="application/ld+json">{!! $_orgSchema !!}</script>
@endpush

@section('content')

{{-- HERO --}}
<section class="relative min-h-[90vh] flex items-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 70%, #4338ca 100%);">
    <div class="absolute inset-0 bg-cover bg-center opacity-10" style='background-image:url("{{ asset('images/background.webp') }}")' alt="Paisaje" class="w-full h-full object-contain" />')"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center w-full">
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-sm font-medium px-4 py-1.5 rounded-full mb-8">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse inline-block"></span>
            Tu proximo evento, perfecto desde el principio
        </div>
        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-tight mb-6">
            Encontra el lugar<br>
            <span style="background:linear-gradient(135deg,#a78bfa,#60a5fa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">perfecto para vos</span>
        </h1>
        <p class="text-lg sm:text-xl text-white/70 mb-12 max-w-2xl mx-auto font-light">
            Un lugar exclusivo de quintas y salones para eventos. Encontra el espacio ideal y coordiná tu reserva fácilmente.
        </p>

        <div class="bg-white rounded-3xl shadow-2xl p-6 max-w-3xl mx-auto"
             x-data="{
                listening: false,
                supported: !!(window.SpeechRecognition || window.webkitSpeechRecognition),
                startVoice() {
                    if (!this.supported) return;
                    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                    const rec = new SR();
                    rec.lang = 'es-AR';
                    rec.interimResults = false;
                    rec.maxAlternatives = 1;
                    this.listening = true;
                    rec.onresult = (e) => {
                        document.getElementById('home-q').value = e.results[0][0].transcript;
                        this.listening = false;
                        this.$nextTick(() => document.getElementById('home-search-form').submit());
                    };
                    rec.onerror = () => { this.listening = false; };
                    rec.onend   = () => { this.listening = false; };
                    rec.start();
                }
             }">
            <form id="home-search-form" action="{{ route('properties.index') }}" method="GET">
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">¿Qué estás buscando?</label>
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                        <input id="home-q" type="text" name="q" autocomplete="off"
                               placeholder="Ej: quinta con pileta en Pilar, salón para 100 personas, casa de campo..."
                               class="w-full pl-12 pr-14 py-4 border border-gray-200 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-gray-700 placeholder-gray-400">
                        <button type="button" x-show="supported" @click="startVoice"
                                :title="listening ? 'Escuchando...' : 'Buscar por voz'"
                                :class="listening
                                    ? 'text-red-500 bg-red-50 animate-pulse'
                                    : 'text-gray-400 hover:text-indigo-600 hover:bg-indigo-50'"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-2 rounded-xl transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4M12 3a4 4 0 014 4v4a4 4 0 01-8 0V7a4 4 0 014-4z"/>
                            </svg>
                        </button>
                    </div>
                    <p x-show="listening" class="text-xs text-red-500 mt-1.5 text-center animate-pulse">Escuchando... hablá ahora</p>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-2xl transition-all duration-200 shadow-lg shadow-indigo-200 hover:shadow-indigo-300">
                    Buscar Propiedades
                </button>
            </form>
        </div>

        @if($stats['properties'] >= 1000)
        <div class="flex items-center justify-center gap-8 mt-12 text-white/60 text-sm">
            <div class="text-center"><div class="text-2xl font-bold text-white">{{ $stats['properties'] }}</div><div>Propiedades</div></div>
            <div class="w-px h-8 bg-white/20"></div>
            <div class="text-center"><div class="text-2xl font-bold text-white">{{ $stats['reservations'] }}</div><div>Reservas</div></div>
            <div class="w-px h-8 bg-white/20"></div>
            <div class="text-center"><div class="text-2xl font-bold text-white">{{ $stats['rating'] ?: '—' }}</div><div>Valoración</div></div>
        </div>
        @endif
    </div>
</section>

@if($featured->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex items-end justify-between mb-10">
        <div>
            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest mb-2">Destacadas</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Propiedades Destacadas</h2>
        </div>
        <a href="{{ route('properties.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            Ver todas <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($featured as $propiedad)
        @include('components.propiedad-card', compact('propiedad'))
        @endforeach
    </div>
</section>
@endif

<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <p class="text-sm font-semibold text-indigo-600 uppercase tracking-widest mb-2">Por que elegirnos?</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">La forma mas simple de reservar</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-8 rounded-3xl hover:bg-indigo-50 transition-colors">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">&#128269;</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Busca y Compara</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Filtra por fecha, capacidad, precio y amenities. Encontra el lugar perfecto para tu evento.</p>
            </div>
            <div class="text-center p-8 rounded-3xl hover:bg-indigo-50 transition-colors">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">&#128197;</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Reserva al Instante</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Verificacion de disponibilidad en tiempo real. Confirma tu reserva en segundos.</p>
            </div>
            <div class="text-center p-8 rounded-3xl hover:bg-indigo-50 transition-colors">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">&#128274;</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Coordiná Directo</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Contactá al propietario, coordiná los detalles y acordá el pago de forma simple y directa.</p>
            </div>
        </div>
    </div>
</section>

@if($latest->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex items-end justify-between mb-10">
        <div>
            <p class="text-sm font-semibold text-purple-600 uppercase tracking-widest mb-2">Nuevas</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Propiedades Recientes</h2>
        </div>
        <a href="{{ route('properties.index') }}?sort=newest" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            Ver todas <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($latest as $propiedad)
        @include('components.propiedad-card', compact('propiedad'))
        @endforeach
    </div>
</section>
@endif

<section class="bg-gradient-to-br from-indigo-600 to-purple-700 py-20 mx-4 sm:mx-8 rounded-3xl mb-20">
    <div class="max-w-3xl mx-auto px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-black text-white mb-4">Tenes una propiedad?</h2>
        <p class="text-indigo-200 text-lg mb-8">Publica tu espacio gratis y empieza a recibir reservas hoy mismo. Gestiona todo desde tu panel. Tu registro tambien incluye web profesional para tu negocio, Chatbot inteligente para tu whatsapp, y muchas novedades más...</p>
        <div class="flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-white hover:bg-gray-50 text-indigo-700 font-bold px-8 py-3.5 rounded-2xl transition-all shadow-lg hover:shadow-xl">
                Publicar mi Propiedad
            </a>
            <a href="{{ route('properties.index') }}" class="border-2 border-white/40 hover:border-white text-white font-semibold px-8 py-3.5 rounded-2xl transition-all">
                Explora las propiedades
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
@vite('resources/js/pages/home.js')
@endpush
