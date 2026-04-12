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
    <div class="absolute inset-0 bg-cover bg-center opacity-10" style='background-image:url("{{ asset('images/background.png') }}")' alt="Paisaje" class="w-full h-full object-contain" />')"></div>
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

        <div class="bg-white rounded-3xl shadow-2xl p-6 max-w-4xl mx-auto">
            <form action="{{ route('properties.index') }}" method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div class="text-left">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Provincia</label>
                        <select name="state" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                            <option value="">Todas las provincias</option>
                            @foreach($provinces as $prov)
                            <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-left">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Ciudad</label>
                        <select name="city" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                            <option value="">Todas las ciudades</option>
                        </select>
                    </div>
                    <div class="text-left">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Tipo de propiedad</label>
                        <select name="type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                            <option value="">Todos los tipos</option>
                            @foreach(\App\Models\Property::typesList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-2xl transition-all duration-200 shadow-lg shadow-indigo-200 hover:shadow-indigo-300">
                        Buscar Propiedades
                    </button>
                </div>
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
        <p class="text-indigo-200 text-lg mb-8">Publica tu espacio gratis y empieza a recibir reservas hoy mismo. Gestiona todo desde tu panel.</p>
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
