@extends('layouts.main')
@section('title', $propiedad->name . ' - ' . $propiedad->city . ', ' . $propiedad->state)
@section('description', $propiedad->short_description ?? \Illuminate\Support\Str::limit(strip_tags($propiedad->description), 160))
@section('keywords', $propiedad->name . ', quintas ' . $propiedad->city . ', espacios para eventos ' . $propiedad->city . ', alquiler quintas ' . $propiedad->state)
@section('og_type', 'article')
@section('og_image', $propiedad->cover_image_url)

@push('head')
@php
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'LocalBusiness',
    'name'        => $propiedad->name,
    'description' => $propiedad->short_description ?? \Illuminate\Support\Str::limit(strip_tags($propiedad->description), 250),
    'image'       => $propiedad->cover_image_url,
    'url'         => url()->current(),
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $propiedad->address,
        'addressLocality' => $propiedad->city,
        'addressRegion'   => $propiedad->state,
        'addressCountry'  => 'AR',
    ],
    'priceRange'         => '$' . number_format($propiedad->price_per_day, 0, ',', '.') . ' ARS/día',
    'currenciesAccepted' => 'ARS',
    'areaServed'         => $propiedad->city . ', ' . $propiedad->state,
];
if ($propiedad->rating > 0) {
    $schema['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => (string) $propiedad->rating,
        'reviewCount' => (string) $propiedad->reviews_count,
        'bestRating'  => '5',
        'worstRating' => '1',
    ];
}
if ($propiedad->latitude && $propiedad->longitude) {
    $schema['geo'] = [
        '@type'     => 'GeoCoordinates',
        'latitude'  => (string) $propiedad->latitude,
        'longitude' => (string) $propiedad->longitude,
    ];
}
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>

@php
$breadcrumbSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',       'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Propiedades',  'item' => route('properties.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $propiedad->name],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>

@if($propiedad->reviews->count())
@php
$reviewsSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'itemListElement' => $propiedad->reviews->map(function ($review, $i) use ($propiedad) {
        return [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => [
                '@type'         => 'Review',
                'author'        => ['@type' => 'Person', 'name' => $review->user->full_name],
                'datePublished' => $review->created_at->toDateString(),
                'reviewRating'  => [
                    '@type'       => 'Rating',
                    'ratingValue' => (string) $review->rating,
                    'bestRating'  => '5',
                    'worstRating' => '1',
                ],
                'reviewBody'    => $review->comment,
                'itemReviewed'  => ['@type' => 'LocalBusiness', 'name' => $propiedad->name],
            ],
        ];
    })->values()->all(),
];
@endphp
<script type="application/ld+json">{!! json_encode($reviewsSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
@endpush

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <ol class="flex items-center gap-2 list-none p-0 m-0">
            <li><a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('properties.index') }}" class="hover:text-indigo-600">Propiedades</a></li>
            <li aria-hidden="true">/</li>
            <li><span class="text-gray-800 font-medium" aria-current="page">{{ $propiedad->name }}</span></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- LEFT: Content --}}
        <div class="lg:col-span-2">
            {{-- Image gallery --}}
            <div class="rounded-2xl overflow-hidden mb-6 aspect-video bg-gray-900 flex items-center justify-center">
                @if($propiedad->images->count())
                <div x-data="{ active: 0, touchStartX: 0 }"
                     @touchstart="touchStartX = $event.touches[0].clientX"
                     @touchend="let diff = touchStartX - $event.changedTouches[0].clientX; if(Math.abs(diff) > 40){ if(diff > 0){ active = (active + 1) % {{ $propiedad->images->count() }} } else { active = (active - 1 + {{ $propiedad->images->count() }}) % {{ $propiedad->images->count() }} } }"
                     class="relative w-full h-full flex items-center justify-center">
                    @foreach($propiedad->images as $i => $image)
                    <img x-show="active === {{ $i }}" src="{{ $image->url }}" alt="{{ $propiedad->name }}"
                         style="max-width:100%;max-height:100%;width:auto;height:auto;display:block;"
                         loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                         onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=80'">
                    @endforeach
                    @if($propiedad->images->count() > 1)
                    <button @click="active = (active - 1 + {{ $propiedad->images->count() }}) % {{ $propiedad->images->count() }}"
                        class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="active = (active + 1) % {{ $propiedad->images->count() }}"
                        class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                        @foreach($propiedad->images as $i => $_)
                        <button @click="active = {{ $i }}" :class="active === {{ $i }} ? 'bg-white w-4' : 'bg-white/50 w-2'"
                            class="h-2 rounded-full transition-all"></button>
                        @endforeach
                    </div>
                    @endif
                </div>
                @else
                <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=80" alt="{{ $propiedad->name }}" style="max-width:100%;max-height:100%;width:auto;height:auto;display:block;">
                @endif
            </div>

            {{-- Header --}}
            <div class="mb-6">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h1 class="text-xl lg:text-3xl font-black text-gray-900">{{ $propiedad->name }}</h1>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($propiedad->rating > 0 && \App\Models\Setting::get('reviews_enabled', '1') === '1')
                        <a href="#reseñas" id="rating-link" class="hidden-sm flex items-center gap-1.5 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl hover:bg-amber-100 transition-colors">
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="font-bold text-amber-700">{{ $propiedad->rating }}</span>
                            <span class="text-amber-600 text-sm hidden-sm">({{ $propiedad->reviews_count }} reseñas)</span>
                        </a>
                        @endif
                        @php $isFav = auth()->check() && auth()->user()->favorites()->where('property_id', $propiedad->id)->exists(); @endphp
                        <div x-data="{ fav: {{ $isFav ? 'true' : 'false' }}, busy: false, sparks: [] }" class="relative">
                            <button type="button"
                                    @click="if(!busy){
                                        @auth
                                        busy=true; fav=!fav;
                                        if(fav){
                                            for(let i=0;i<7;i++){
                                                sparks.push({ id: Date.now()+i, x: (Math.random()*44)-22, delay: i*90 });
                                            }
                                            setTimeout(()=>{ sparks=[] }, 1200);
                                        }
                                        fetch('{{ route('favorites.toggle', $propiedad->slug) }}', { method:'POST', headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' } }).finally(()=>{ busy=false })
                                        @else
                                        window.location='{{ route('favorites.login-and-save', $propiedad->slug) }}'
                                        @endauth
                                    }"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all hover:scale-105"
                                    :class="fav ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-400 hover:text-red-400 hover:border-red-200'"
                                    title="Guardar en favoritos">
                                <svg class="w-5 h-5" :fill="fav ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="text-sm font-medium guardar-text hidden-sm" x-text="fav ? 'Guardado' : 'Guardar'"></span>
                            </button>
                            <template x-for="s in sparks" :key="s.id">
                                <span class="float-heart text-red-500 text-xs select-none"
                                      :style="`left:calc(50% + ${s.x}px - 6px); bottom:50%; animation-delay:${s.delay}ms;`">❤</span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-gray-500 text-sm flex-wrap">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        @if($propiedad->street_name)
                            {{ $propiedad->street_name }} {{ $propiedad->street_number }}, {{ $propiedad->locality }}, {{ $propiedad->partido }}, {{ $propiedad->state }}, {{ $propiedad->country ?: 'Argentina' }}
                        @else
                            {{ $propiedad->address }}, {{ $propiedad->city }}, {{ $propiedad->state }}
                        @endif
                    </span>
                    @php
                        $mapsQuery = urlencode(trim($propiedad->address . ', ' . $propiedad->city . ', ' . $propiedad->state . ', Argentina'));
                        $mapsUrl = $propiedad->map_url
                            ?: ($propiedad->latitude && $propiedad->longitude
                                ? 'https://www.google.com/maps?q=' . $propiedad->latitude . ',' . $propiedad->longitude
                                : 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery);
                    @endphp
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Ver en Google Maps
                    </a>
                </div>
            </div>

            {{-- Precios (mobile only, se muestra via JS) --}}
            <div id="mobile-prices" style="display:none;margin-bottom:20px;">
                <div style="background:#eef2ff;border-radius:16px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;">
                    <p style="font-weight:800;color:#4338ca;font-size:14px;margin:0;">Precio por día</p>
                    <p style="font-weight:800;color:#4338ca;font-size:20px;margin:0;">${{ number_format($propiedad->price_per_day, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-8">
                <div class="bg-indigo-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">👥</div>
                    <div class="text-2xl font-bold text-indigo-700">{{ $propiedad->capacity }}</div>
                    <div class="text-xs text-indigo-500 mt-0.5">Personas</div>
                </div>
                <div class="bg-purple-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">🏠</div>
                    <div class="text-2xl font-bold text-purple-700">{{ $propiedad->rooms }}</div>
                    <div class="text-xs text-purple-500 mt-0.5">Ambientes</div>
                </div>
                <div class="bg-sky-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">🛏️</div>
                    <div class="text-2xl font-bold text-sky-700">{{ $propiedad->beds }}</div>
                    <div class="text-xs text-sky-500 mt-0.5">Camas</div>
                </div>
                <div class="bg-pink-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">🚿</div>
                    <div class="text-2xl font-bold text-pink-700">{{ $propiedad->bathrooms }}</div>
                    <div class="text-xs text-pink-500 mt-0.5">Baños</div>
                </div>
                <div class="bg-orange-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl mb-1">🚗</div>
                    <div class="text-2xl font-bold text-orange-700">{{ $propiedad->parking_spots }}</div>
                    <div class="text-xs text-orange-500 mt-0.5">Estacionamientos</div>
                </div>
            </div>

            {{-- Description --}}
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-3">Descripción</h2>
                <p class="text-gray-600 leading-relaxed">{{ $propiedad->description }}</p>
            </div>

            {{-- Amenities --}}
            @if($propiedad->amenities && count($propiedad->amenities))
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Comodidades</h2>
                @php $amenitiesAll = \App\Models\Property::amenitiesList(); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($propiedad->amenities as $amenity)
                    <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                        @if(isset($amenitiesAll[$amenity]))
                            <span class="text-xl">{{ $amenitiesAll[$amenity]['icon'] }}</span>
                            <span class="text-sm font-medium text-gray-700">{{ $amenitiesAll[$amenity]['label'] }}</span>
                        @else
                            <span class="text-xl">✔</span>
                            <span class="text-sm font-medium text-gray-700">{{ $amenity }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Servicios adicionales --}}
            @if($propiedad->services->count())
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Servicios adicionales</h2>
                <div class="space-y-2">
                    @foreach($propiedad->services as $service)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                        <span class="text-sm font-medium text-gray-700">{{ $service->name }}</span>
                        <span class="text-sm font-semibold text-indigo-700">
                            ${{ number_format($service->price, 0, ',', '.') }}
                            <span class="text-xs text-gray-400 font-normal">× {{ rtrim(rtrim(number_format($service->quantity, 2, ',', '.'), '0'), ',') }} {{ $service->unit }}</span>
                        </span>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-3">* Los servicios son opcionales y se acuerdan con el propietario al confirmar la reserva.</p>
            </div>
            @endif

            {{-- Rules --}}
            @if($propiedad->rules && count($propiedad->rules))
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Reglas del espacio</h2>
                <ul class="space-y-2">
                    @foreach($propiedad->rules as $rule)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $rule }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </div>

        {{-- RIGHT: Booking widget --}}
        <div class="lg:col-span-1" id="booking-col">
            <div class="sticky top-20">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6">
                    {{-- Precio por día --}}
                    <div id="booking-price-grid" class="mb-5">
                        <div class="bg-indigo-50 rounded-2xl p-4 text-center">
                            <p style="font-weight:800;color:#4338ca;font-size:14px;margin:5px;">Precio por día</p>
                            <p class="font-black text-indigo-700 text-2xl">${{ number_format($propiedad->price_per_day, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('reservations.store', $propiedad->slug) }}" method="POST"
                          x-data="bookingForm" @submit.prevent="submitForm">
                        @csrf
                        <div class="space-y-3 mb-4">
                            {{-- Fecha y hora de entrada --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Entrada</label>
                                <div class="flex gap-2">
                                    <input type="text" id="fp_check_in" name="check_in"
                                        x-model="checkIn" @change="calculateTotal(); errors.checkIn = ''" readonly
                                        placeholder="dd/mm/aaaa"
                                        :class="errors.checkIn ? 'border-red-400' : 'border-gray-200'"
                                        class="flex-1 px-3 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer bg-white">
                                    <input type="time" name="check_in_time" x-model="checkInTime" @change="calculateTotal()" required
                                        class="w-28 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <p x-show="errors.checkIn" x-text="errors.checkIn" class="text-red-500 text-xs mt-1"></p>
                                @error('check_in')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            {{-- Fecha y hora de salida --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Salida</label>
                                <div class="flex gap-2">
                                    <input type="text" id="fp_check_out" name="check_out"
                                        x-model="checkOut" @change="calculateTotal(); errors.checkOut = ''" readonly
                                        placeholder="dd/mm/aaaa"
                                        :class="errors.checkOut ? 'border-red-400' : 'border-gray-200'"
                                        class="flex-1 px-3 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer bg-white">
                                    <input type="time" name="check_out_time" x-model="checkOutTime" @change="calculateTotal()" required
                                        class="w-28 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <p x-show="errors.checkOut" x-text="errors.checkOut" class="text-red-500 text-xs mt-1"></p>
                                @error('check_out')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            {{-- Cantidad de personas --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cantidad de personas</label>
                                <input type="number" name="guests" min="1" max="{{ $propiedad->max_guests ?? '' }}"
                                    value="{{ old('guests') }}" placeholder="0"
                                    @change="errors.guests = ''"
                                    :class="errors.guests ? 'border-red-400' : 'border-gray-200'"
                                    class="w-full px-3 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <p x-show="errors.guests" x-text="errors.guests" class="text-red-500 text-xs mt-1"></p>
                                @error('guests')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Hint descuentos por estadía --}}
                        @php
                            $dayDiscounts = collect($propiedad->day_discounts ?? [])
                                ->filter(fn($t) => !empty($t['days']) && !empty($t['discount']))
                                ->sortBy(fn($t) => (int)$t['days'])
                                ->values();
                        @endphp
                        @if($dayDiscounts->isNotEmpty())
                        <p class="text-xs text-indigo-500 mb-3 flex items-center gap-1 flex-wrap">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            {{ $dayDiscounts->map(fn($t) => '+' . (int)$t['days'] . ' días ' . (int)$t['discount'] . '% OFF')->implode(' · ') }}
                        </p>
                        @endif


                        {{-- Detalle de precios --}}
                        <template x-if="breakdown.length > 0">
                            <div class="bg-gray-50 rounded-xl p-3 mb-4 space-y-1.5">
                                <template x-for="line in breakdown" :key="line.label">
                                    <div class="flex justify-between text-sm">
                                        <span :class="line.type === 'discount' ? 'text-red-500' : 'text-gray-600'" x-text="line.label"></span>
                                        <span :class="line.type === 'discount' ? 'text-red-500 font-medium' : 'text-gray-700 font-medium'"
                                              x-text="(line.amount < 0 ? '-' : '') + '$' + fmt(Math.abs(line.amount))"></span>
                                    </div>
                                </template>
                                <div class="border-t border-gray-200 pt-1.5 flex justify-between text-sm font-bold text-gray-900">
                                    <span>Total</span>
                                    <span x-text="'$' + fmt(total) + ' ARS'"></span>
                                </div>
                            </div>
                        </template>

                        @if($propiedad->min_days || $propiedad->max_days)
                        <p class="text-xs text-gray-400 mb-3 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Estadía:
                            @if($propiedad->min_days) mínimo {{ $propiedad->min_days }} noche{{ $propiedad->min_days > 1 ? 's' : '' }}@endif
                            @if($propiedad->min_days && $propiedad->max_days) · @endif
                            @if($propiedad->max_days) máximo {{ $propiedad->max_days }} noche{{ $propiedad->max_days > 1 ? 's' : '' }}@endif
                        </p>
                        @endif

                        <p x-show="errors.days" x-text="errors.days" class="text-red-500 text-xs mb-3"></p>

                        @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
                            @foreach($errors->all() as $error)
                            <p class="text-red-600 text-xs">{{ $error }}</p>
                            @endforeach
                        </div>
                        @endif

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-2xl transition-all shadow-lg shadow-indigo-200 hover:shadow-indigo-300">
                            Solicitar Reserva
                        </button>

                        <p class="text-xs text-gray-400 text-center mt-3">Al reservar coordinarás el pago directamente con el propietario.</p>
                    </form>

                    <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Reserva segura y verificada
                    </div>
                </div>

                {{-- Owner contact --}}
                @php
                    $ownerWa = $propiedad->owner->whatsapp_link
                        ?: ($propiedad->owner->phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $propiedad->owner->phone) : null);
                @endphp
                <div class="mt-4 bg-gray-50 rounded-2xl p-4 flex items-center gap-3">
                    <img src="{{ $propiedad->owner->avatar_url }}" class="w-10 h-10 rounded-full" alt="{{ $propiedad->owner->full_name }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500">Publicado por</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $propiedad->owner->full_name }}</p>
                        <div class="flex flex-wrap gap-3 mt-1">
                            @if($propiedad->owner->phone)
                            <a href="tel:{{ $propiedad->owner->phone }}" class="text-xs text-gray-500 hover:text-indigo-600 flex items-center gap-1 transition-colors">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $propiedad->owner->phone }}
                            </a>
                            @endif
                            @if($ownerWa)
                            <a href="{{ $ownerWa }}" target="_blank" class="text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1 transition-colors">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- REVIEWS: last on mobile, spans left 2 cols on desktop --}}
        @if(\App\Models\Setting::get('reviews_enabled', '1') === '1')
        <div id="reseñas" class="lg:col-span-2">
            <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-bold text-gray-900">
                        Reseñas
                        @if($propiedad->reviews->count())
                        <span class="text-base font-normal text-gray-400">({{ $propiedad->reviews->count() }})</span>
                        @endif
                    </h2>
                    @if($propiedad->rating > 0)
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($propiedad->rating) ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                        <span class="font-bold text-gray-800 ml-1">{{ $propiedad->rating }}</span>
                    </div>
                    @endif
                </div>

                @if($propiedad->reviews->count())
                <div class="space-y-4" x-data="{ showAll: false }">
                    @foreach($propiedad->reviews as $index => $review)
                    <div x-show="showAll || {{ $index }} < 4" class="bg-gray-50 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $review->user->avatar_url }}" class="w-9 h-9 rounded-full object-cover" alt="{{ $review->user->full_name }}">
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">{{ $review->user->full_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $review->comment }}</p>
                    </div>
                    @endforeach

                    @if($propiedad->reviews->count() > 4)
                    <button @click="showAll = !showAll"
                        class="w-full py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                        x-text="showAll ? 'Ver menos reseñas' : 'Ver todas las reseñas ({{ $propiedad->reviews->count() }})'">
                    </button>
                    @endif
                </div>
                @else
                <div class="bg-gray-50 rounded-2xl p-8 text-center">
                    <div class="text-4xl mb-3">&#128172;</div>
                    <p class="text-gray-500 text-sm">Aún no hay reseñas para esta propiedad.</p>
                    <p class="text-gray-400 text-xs mt-1">¡Sé el primero en opinar!</p>
                </div>
                @endif

                {{-- Formulario para dejar reseña --}}
                @if($reservaParaReseña)
                <div class="mt-6 bg-indigo-50 border border-indigo-100 rounded-2xl p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Dejá tu reseña</h3>
                    <p class="text-sm text-gray-500 mb-4">Contanos tu experiencia con esta propiedad.</p>
                    <form action="{{ route('reviews.store', $reservaParaReseña) }}" method="POST">
                        @csrf
                        <div class="mb-4" x-data="{ rating: 0, hover: 0 }">
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Puntuación</label>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $i }}" class="sr-only" required>
                                    <svg @click="rating = {{ $i }}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0"
                                        :class="(hover || rating) >= {{ $i }} ? 'text-amber-400' : 'text-gray-300'"
                                        class="w-8 h-8 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </label>
                                @endfor
                            </div>
                            @error('rating')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tu comentario</label>
                            <textarea name="comment" rows="3" required minlength="10" maxlength="1000"
                                placeholder="Contanos qué te pareció el lugar, la atención, la limpieza..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none bg-white">{{ old('comment') }}</textarea>
                            @error('comment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                            Enviar reseña
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- Similar propiedades --}}
    @if($similarPropiedades->count())
    <div class="mt-16 pt-8 border-t border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Propiedades similares en {{ $propiedad->city }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($similarPropiedades as $similar)
            @include('components.propiedad-card', ['propiedad' => $similar])
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- FAB Reservar (mobile) --}}
<div id="fab-reservar" style="display:none;position:fixed;bottom:0;left:0;right:0;padding:12px 16px 20px;background:linear-gradient(to top,#fff 80%,rgba(255,255,255,0));z-index:9999;">
    <button onclick="openBookingSheet()" style="width:100%;display:flex;align-items:center;justify-content:center;gap:10px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;font-weight:700;padding:18px;border-radius:18px;border:none;font-size:17px;cursor:pointer;box-shadow:0 8px 24px rgba(79,70,229,0.4);letter-spacing:0.01em;">
        <svg style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Reservar
    </button>
</div>

{{-- Bottom sheet (mobile) --}}
<div id="booking-sheet" style="display:none;position:fixed;inset:0;z-index:9998;">
    <div onclick="closeBookingSheet()" style="position:absolute;inset:0;background:rgba(15,15,30,0.55);backdrop-filter:blur(2px);"></div>
    <div id="sheet-inner" style="position:absolute;bottom:0;left:0;right:0;background:#fff;border-radius:28px 28px 0 0;max-height:88vh;overflow-y:auto;transform:translateY(100%);transition:transform 0.35s cubic-bezier(0.32,0.72,0,1);padding:0 20px 32px;">
        <div style="position:sticky;top:0;background:#fff;padding:12px 0 8px;z-index:1;">
            <div style="width:36px;height:4px;background:#e5e7eb;border-radius:2px;margin:0 auto 12px;"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <h3 style="font-size:18px;font-weight:800;color:#111827;margin:0 0 6px;">Solicitar reserva</h3>
                    <div style="display:inline-flex;align-items:baseline;gap:4px;background:#eef2ff;border-radius:12px;padding:5px 12px;">
                        <span style="font-size:22px;font-weight:900;color:#4338ca;">${{ number_format($propiedad->price_per_day, 0, ',', '.') }}</span>
                        <span style="font-size:12px;font-weight:600;color:#6366f1;">/ día</span>
                    </div>
                </div>
                <button onclick="closeBookingSheet()" style="width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div id="sheet-form-slot"></div>
    </div>
</div>

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@vite(['resources/css/pages/propiedades-show.css'])
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
window.SHOW_UNAVAILABLE_DATES  = @json($unavailableDates);
window.SHOW_PRICE_PER_HOUR     = {{ $propiedad->price_per_hour ?? 0 }};
window.SHOW_PRICE_PER_DAY      = {{ $propiedad->price_per_day ?? 0 }};
window.SHOW_MIN_DAYS           = {{ $propiedad->min_days ?? 'null' }};
window.SHOW_MAX_DAYS           = {{ $propiedad->max_days ?? 'null' }};
window.SHOW_DAY_DISCOUNTS      = {!! json_encode($propiedad->day_discounts    ?? []) !!};
window.SHOW_DATE_DISCOUNTS     = {!! json_encode($propiedad->date_discounts   ?? []) !!};
window.SHOW_WEEKDAY_DISCOUNTS  = {!! json_encode($propiedad->weekday_discounts ?? []) !!};
window.SHOW_IS_LOGGED_IN       = {{ auth()->check() ? 'true' : 'false' }};
window.SHOW_PENDING_RESERVATION= @json($pendingReservation ?? null);
</script>
@vite(['resources/js/pages/propiedades-show.js'])
@endpush

@endsection
