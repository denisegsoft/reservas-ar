@extends('layouts.main')
@section('title', 'Buscar Propiedades' . (request('state') ? ' en ' . request('state') : ''))
@section('description', 'Buscá y reservá quintas, salones y espacios para eventos en Argentina. Filtrá por provincia, ciudad, capacidad y precio. Encontrá el lugar perfecto para tu evento.')
@section('keywords', 'quintas para eventos Argentina, salones de fiestas, alquiler quintas, buscar quintas' . (request('state') ? ', quintas ' . request('state') : ''))
@section('canonical', route('properties.index', array_filter(['state' => request('state'), 'city' => request('city'), 'type' => request('type'), 'guests' => request('guests')])))
@section('content')

@php
    $currentSort = request('sort', 'featured');
    $isDesc = str_ends_with($currentSort, '_desc');
    $baseSort = preg_replace('/_(asc|desc)$/', '', $currentSort);
    $canToggle = in_array($baseSort, ['price', 'rating']);
    $toggleSort = $canToggle
        ? ($isDesc ? $baseSort . '_asc' : $baseSort . '_desc')
        : $currentSort;
    $toggleQuery = array_merge(request()->except('sort'), ['sort' => $toggleSort]);
    $hasFilters = request()->hasAny(['q','state','partido','locality','type','guests','price_min','price_max','amenities','bedrooms','bathrooms','parking','rating_min','check_in','check_out']);
    $activeFilterCount = collect([
        request('q'), request('state'), request('partido'), request('locality'), request('type'), request('guests'),
        (request('price_min') || request('price_max')) ? '1' : null,
        request('rating_min'), request('bedrooms'), request('bathrooms'), request('parking'),
        (request('check_in') || request('check_out')) ? '1' : null,
    ])->filter()->count() + count((array) request('amenities', []));
@endphp

<div class="min-h-screen bg-gray-100">

    {{-- ================================================================ --}}
    {{-- MOBILE ONLY: Sticky action bar                                    --}}
    {{-- ================================================================ --}}
    <div class="sticky top-16 z-30 bg-white border-b border-gray-200 shadow-sm lg:hidden">
        <div class="px-4 sm:px-6 flex items-center justify-between h-14 gap-4">
            <button type="button"
                    data-bs-toggle="modal" data-bs-target="#filtrosModal"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filtros
                @if($hasFilters)
                <span class="bg-indigo-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $activeFilterCount }}</span>
                @endif
            </button>
            <div class="flex items-center gap-2">
                <label for="">Ordenar por</label>
                <select id="sort-select-mobile" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                    <option value="featured" {{ $baseSort=='featured' ? 'selected' : '' }}>Destacados</option>
                    <option value="price"    {{ $baseSort=='price'    ? 'selected' : '' }}>Precio</option>
                    <option value="rating"   {{ $baseSort=='rating'   ? 'selected' : '' }}>Valoración</option>
                    <option value="newest"   {{ $baseSort=='newest'   ? 'selected' : '' }}>Más recientes</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MOBILE ONLY: Filter Modal (Bootstrap JS + Tailwind design)        --}}
    {{-- ================================================================ --}}
    <div class="modal fade lg:hidden" id="filtrosModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">

            <form method="GET" action="{{ route('properties.index') }}"
                  class="modal-content" style="border-radius:1rem;border:none">

                {{-- Header --}}
                <div class="modal-header flex items-center justify-between px-5 py-4 border-b border-gray-100" style="border-radius:1rem 1rem 0 0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-900">Filtros</span>
                        @if($hasFilters)
                        <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $activeFilterCount }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        @if($hasFilters)
                        <a href="{{ route('properties.index') }}" class="text-xs text-red-500 hover:text-red-600 font-medium">Limpiar</a>
                        @endif
                        <button type="button" data-bs-dismiss="modal"
                                class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="modal-body p-0">

                    {{-- Búsqueda por texto --}}
                    <div class="px-5 py-4 border-b border-gray-100"
                         x-data="{
                            listening: false,
                            supported: !!(window.SpeechRecognition || window.webkitSpeechRecognition),
                            startVoice() {
                                if (!this.supported) return;
                                const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                                const rec = new SR(); rec.lang = 'es-AR'; rec.interimResults = false;
                                this.listening = true;
                                rec.onresult = (e) => {
                                    document.getElementById('modal-q').value = e.results[0][0].transcript;
                                    this.listening = false;
                                    this.$nextTick(() => this.$el.closest('form').submit());
                                };
                                rec.onerror = rec.onend = () => { this.listening = false; };
                                rec.start();
                            }
                         }">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Buscar</p>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                            <input id="modal-q" type="text" name="q" value="{{ request('q') }}" autocomplete="off"
                                   placeholder="Pileta, Pilar, salón, quincho..."
                                   class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-gray-700 placeholder-gray-400">
                            <button type="button" x-show="supported" @click="startVoice"
                                    :title="listening ? 'Escuchando...' : 'Buscar por voz'"
                                    :class="listening ? 'text-red-500 animate-pulse' : 'text-gray-400 hover:text-indigo-600'"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 rounded-lg transition-colors focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4M12 3a4 4 0 014 4v4a4 4 0 01-8 0V7a4 4 0 014-4z"/>
                                </svg>
                            </button>
                        </div>
                        <p x-show="listening" class="text-xs text-red-500 mt-1.5 animate-pulse">Escuchando... hablá ahora</p>
                    </div>

                    {{-- Ubicación --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ubicación</p>
                        <div class="space-y-2">
                            <select name="state" id="modal-state" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="">Todas las provincias</option>
                                @foreach($provinces as $prov)
                                <option value="{{ $prov }}" {{ request('state')==$prov ? 'selected' : '' }}>{{ $prov }}</option>
                                @endforeach
                            </select>
                            <select name="partido" id="modal-partido" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="">Todos los partidos</option>
                                @foreach($partidos as $p)
                                <option value="{{ $p->name }}" {{ request('partido')==$p->name ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <select name="locality" id="modal-locality" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="">Todas las localidades</option>
                                @foreach($localidades as $loc)
                                <option value="{{ $loc }}" {{ request('locality')==$loc ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tipo --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Tipo de propiedad</p>
                        <div class="flex flex-wrap gap-2">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="type" value="" {{ !request('type') ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Todos</span>
                            </label>
                            @foreach($typesList as $value => $label)
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="type" value="{{ $value }}" {{ request('type')==$value ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Precio --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Precio por día</p>
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Mínimo</label>
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                        <input type="number" name="price_min" value="{{ request('price_min') }}" min="0" placeholder="0" class="w-full pl-6 pr-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Máximo</label>
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                        <input type="number" name="price_max" value="{{ request('price_max') }}" min="0" placeholder="∞" class="w-full pl-6 pr-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                Aplicar precio
                            </button>
                        </div>
                    </div>

                    {{-- Capacidad y características --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Capacidad y características</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Personas (mínimo)</label>
                                <div class="flex gap-2">
                                    <input type="text" name="guests" value="{{ request('guests') }}" placeholder="Ej: 50" inputmode="numeric" pattern="[0-9]*" class="flex-1 min-w-0 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <button type="submit" class="flex items-center justify-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors whitespace-nowrap">

                                        Aplicar
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1.5 font-medium">Habitaciones</label>
                                    <select name="bedrooms" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                        <option value="">-</option>
                                        @foreach([1,2,3,4,5] as $n)<option value="{{ $n }}" {{ request('bedrooms')==$n ? 'selected' : '' }}>{{ $n }}+</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1.5 font-medium">Baños</label>
                                    <select name="bathrooms" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                        <option value="">-</option>
                                        @foreach([1,2,3,4] as $n)<option value="{{ $n }}" {{ request('bathrooms')==$n ? 'selected' : '' }}>{{ $n }}+</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1.5 font-medium">Parking</label>
                                    <select name="parking" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                        <option value="">-</option>
                                        @foreach([1,5,10,20,50] as $n)<option value="{{ $n }}" {{ request('parking')==$n ? 'selected' : '' }}>{{ $n }}+</option>@endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Valoración --}}
                    <div class="px-5 py-4 border-b border-gray-100" x-data="{ rating: {{ request('rating_min', 0) }} }">
                        <input type="hidden" name="rating_min" :value="rating || ''">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Valoración mínima</p>
                        <div class="flex items-center gap-1">
                            @foreach([1,2,3,4,5] as $star)
                            <button type="button" @click="rating = (rating === {{ $star }} ? 0 : {{ $star }}); $nextTick(() => window.filtersAutoSubmit($el.closest('form')))"
                                    class="text-2xl transition-transform hover:scale-110 focus:outline-none"
                                    :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-gray-200'">★</button>
                            @endforeach
                            <span x-show="rating > 0" class="text-xs text-gray-500 ml-1" x-text="rating + '+ estrellas'"></span>
                        </div>
                    </div>

                    {{-- Disponibilidad --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Disponibilidad</p>
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Fecha entrada</label>
                                    <input type="date" name="check_in" value="{{ request('check_in') }}" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Hora entrada</label>
                                    <input type="time" name="check_in_time" value="{{ request('check_in_time') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Fecha salida</label>
                                    <input type="date" name="check_out" value="{{ request('check_out') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Hora salida</label>
                                    <input type="time" name="check_out_time" value="{{ request('check_out_time') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                            </div>
                            <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                Buscar disponibilidad
                            </button>
                        </div>
                    </div>

                    {{-- Comodidades --}}
                    <div class="px-5 py-4">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Comodidades</p>
                        <div class="grid grid-cols-2 gap-y-2">
                            @foreach($amenitiesList as $key => $amenity)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="amenities[]" value="{{ $key }}"
                                       {{ in_array($key, (array) request('amenities', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-600">{{ $amenity['label'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>{{-- end scrollable body --}}

            </form>{{-- modal-content --}}
        </div>{{-- modal-dialog --}}
    </div>{{-- #filtrosModal --}}

    {{-- ================================================================ --}}
    {{-- PAGE CONTENT                                                       --}}
    {{-- ================================================================ --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-8">

            {{-- DESKTOP ONLY: Sticky sidebar --}}
            <aside class="hidden lg:block w-72 flex-shrink-0">
                <div class="sticky top-20">
                    <form method="GET" action="{{ route('properties.index') }}" id="filter-form">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                                <span class="font-bold text-gray-900 text-sm">Filtros</span>
                                @if($hasFilters)
                                <a href="{{ route('properties.index') }}" class="text-xs text-red-500 hover:text-red-600 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Limpiar
                                </a>
                                @endif
                            </div>

                            {{-- Búsqueda por texto --}}
                            <div class="px-5 py-4 border-b border-gray-100"
                                 x-data="{
                                    listening: false,
                                    supported: !!(window.SpeechRecognition || window.webkitSpeechRecognition),
                                    startVoice() {
                                        if (!this.supported) return;
                                        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                                        const rec = new SR(); rec.lang = 'es-AR'; rec.interimResults = false;
                                        this.listening = true;
                                        rec.onresult = (e) => {
                                            document.getElementById('sidebar-q').value = e.results[0][0].transcript;
                                            this.listening = false;
                                            this.$nextTick(() => document.getElementById('filter-form').submit());
                                        };
                                        rec.onerror = rec.onend = () => { this.listening = false; };
                                        rec.start();
                                    }
                                 }">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Buscar</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                                        </svg>
                                        <input id="sidebar-q" type="text" name="q" value="{{ request('q') }}" autocomplete="off"
                                               placeholder="Pileta, Pilar, salón..."
                                               class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-gray-700 placeholder-gray-400">
                                        <button type="button" x-show="supported" @click="startVoice"
                                                :title="listening ? 'Escuchando...' : 'Buscar por voz'"
                                                :class="listening ? 'text-red-500 animate-pulse' : 'text-gray-400 hover:text-indigo-600'"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 rounded-lg transition-colors focus:outline-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4M12 3a4 4 0 014 4v4a4 4 0 01-8 0V7a4 4 0 014-4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="submit" class="flex items-center justify-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                    </button>
                                </div>
                                <p x-show="listening" class="text-xs text-red-500 mt-1.5 animate-pulse">Escuchando... hablá ahora</p>
                            </div>

                            {{-- Ubicación --}}
                            <div class="px-5 py-4 border-b border-gray-100" x-data="{ open: true }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ubicación</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3 space-y-2">
                                    <select name="state" id="sidebar-state" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                                        <option value="">Todas las provincias</option>
                                        @foreach($provinces as $prov)
                                        <option value="{{ $prov }}" {{ request('state')==$prov ? 'selected' : '' }}>{{ $prov }}</option>
                                        @endforeach
                                    </select>
                                    <select name="partido" id="sidebar-partido" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                                        <option value="">Todos los partidos</option>
                                        @foreach($partidos as $p)
                                        <option value="{{ $p->name }}" {{ request('partido')==$p->name ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="locality" id="sidebar-locality" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                                        <option value="">Todas las localidades</option>
                                        @foreach($localidades as $loc)
                                        <option value="{{ $loc }}" {{ request('locality')==$loc ? 'selected' : '' }}>{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tipo --}}
                            <div class="px-5 py-4 border-b border-gray-100" x-data="{ open: true }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de propiedad</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3 space-y-1.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="type" value="" {{ !request('type') ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">Todos</span>
                                    </label>
                                    @foreach($typesList as $value => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="type" value="{{ $value }}" {{ request('type')==$value ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Precio --}}
                            <div class="px-5 py-4 border-b border-gray-100" x-data="{ open: true }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Precio por día</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3 space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Mínimo</label>
                                            <div class="relative"><span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                <input type="number" name="price_min" value="{{ request('price_min') }}" min="0" placeholder="0" class="w-full pl-6 pr-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Máximo</label>
                                            <div class="relative"><span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                <input type="number" name="price_max" value="{{ request('price_max') }}" min="0" placeholder="∞" class="w-full pl-6 pr-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                        Aplicar precio
                                    </button>
                                </div>
                            </div>

                            {{-- Capacidad y características --}}
                            <div class="px-5 py-4 border-b border-gray-100" x-data="{ open: true }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Capacidad y características</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3 space-y-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1.5 font-medium">Personas (mínimo)</label>
                                        <div class="flex gap-2">
                                            <input type="text" name="guests" value="{{ request('guests') }}" placeholder="Ej: 50" inputmode="numeric" pattern="[0-9]*" class="flex-1 min-w-0 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                            <button type="submit" class="flex items-center justify-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors whitespace-nowrap">
                                                Aplicar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Hab.</label>
                                            <select name="bedrooms" class="w-full px-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                                <option value="">-</option>
                                                @foreach([1,2,3,4,5] as $n)<option value="{{ $n }}" {{ request('bedrooms')==$n ? 'selected' : '' }}>{{ $n }}+</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Baños</label>
                                            <select name="bathrooms" class="w-full px-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                                <option value="">-</option>
                                                @foreach([1,2,3,4] as $n)<option value="{{ $n }}" {{ request('bathrooms')==$n ? 'selected' : '' }}>{{ $n }}+</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Park.</label>
                                            <select name="parking" class="w-full px-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                                <option value="">-</option>
                                                @foreach([1,5,10,20,50] as $n)<option value="{{ $n }}" {{ request('parking')==$n ? 'selected' : '' }}>{{ $n }}+</option>@endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Valoración --}}
                            <div class="px-5 py-4 border-b border-gray-100" x-data="{ open: true, rating: {{ request('rating_min', 0) }} }">
                                <input type="hidden" name="rating_min" :value="rating || ''">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Valoración mínima</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3">
                                    <div class="flex items-center gap-1">
                                        @foreach([1,2,3,4,5] as $star)
                                        <button type="button" @click="rating = (rating === {{ $star }} ? 0 : {{ $star }}); $nextTick(() => window.filtersAutoSubmit($el.closest('form')))"
                                                class="text-2xl transition-transform hover:scale-110 focus:outline-none"
                                                :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-gray-200'">★</button>
                                        @endforeach
                                        <span x-show="rating > 0" class="text-xs text-gray-500 ml-1" x-text="rating + '+ estrellas'"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Disponibilidad --}}
                            <div class="px-5 py-4 border-b border-gray-100" x-data="{ open: true }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Disponibilidad</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3 space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Fecha entrada</label>
                                            <input type="date" name="check_in" value="{{ request('check_in') }}" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Hora entrada</label>
                                            <input type="time" name="check_in_time" value="{{ request('check_in_time') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Fecha salida</label>
                                            <input type="date" name="check_out" value="{{ request('check_out') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Hora salida</label>
                                            <input type="time" name="check_out_time" value="{{ request('check_out_time') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                        Buscar disponibilidad
                                    </button>
                                </div>
                            </div>

                            {{-- Comodidades --}}
                            <div class="px-5 py-4" x-data="{ open: true }">
                                <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Comodidades</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-3 space-y-2">
                                    @foreach($amenitiesList as $key => $amenity)
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="checkbox" name="amenities[]" value="{{ $key }}"
                                               {{ in_array($key, (array) request('amenities', [])) ? 'checked' : '' }}
                                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">{{ $amenity['label'] }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                    </form>
                </div>
            </aside>

            {{-- MAIN CONTENT --}}
            <div class="flex-1 min-w-0">

                {{-- Desktop top bar --}}
                <div class="hidden lg:flex items-center justify-between mb-6 gap-4 flex-wrap">
                    <h1 class="text-gray-600 text-sm font-normal">
                        <span class="font-semibold text-gray-900">{{ $propiedades->total() }}</span> propiedades encontradas
                        @if(request('state')) en <span class="font-semibold">{{ request('state') }}</span>@endif
                    </h1>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-500 whitespace-nowrap">Ordenar</span>
                        <select id="sort-select" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                            <option value="featured" {{ $baseSort=='featured' ? 'selected' : '' }}>Destacados</option>
                            <option value="price"    {{ $baseSort=='price'    ? 'selected' : '' }}>Precio</option>
                            <option value="rating"   {{ $baseSort=='rating'   ? 'selected' : '' }}>Valoración</option>
                            <option value="newest"   {{ $baseSort=='newest'   ? 'selected' : '' }}>Más recientes</option>
                        </select>
                        @if($canToggle)
                        <a href="{{ route('properties.index', $toggleQuery) }}" title="{{ $isDesc ? 'Menor a mayor' : 'Mayor a menor' }}"
                           class="flex items-center justify-center w-9 h-9 border border-indigo-200 rounded-xl bg-indigo-50 hover:bg-indigo-100 transition-colors text-indigo-500">
                            @if($isDesc)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12"/></svg>
                            @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21l3.75-3.75"/></svg>
                            @endif
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Active filter badges --}}
                @if($hasFilters)
                <div class="flex flex-wrap gap-2 mb-5">
                    @if(request('state'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ request('state') }}<a href="{{ route('properties.index', array_merge(request()->except('state','city'), [])) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('partido'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ request('partido') }}<a href="{{ route('properties.index', request()->except('partido','locality')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('locality'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ request('locality') }}<a href="{{ route('properties.index', request()->except('locality')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('type'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ $typesList[request('type')] ?? request('type') }}<a href="{{ route('properties.index', request()->except('type')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('guests'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ request('guests') }}+ personas<a href="{{ route('properties.index', request()->except('guests')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('price_min') || request('price_max'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        ${{ request('price_min','0') }} – ${{ request('price_max','∞') }}<a href="{{ route('properties.index', request()->except('price_min','price_max')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('bedrooms'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ request('bedrooms') }}+ hab.<a href="{{ route('properties.index', request()->except('bedrooms')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('bathrooms'))
                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                        {{ request('bathrooms') }}+ baños<a href="{{ route('properties.index', request()->except('bathrooms')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @if(request('rating_min'))
                    <span class="inline-flex items-center gap-1 text-xs bg-amber-50 text-amber-700 border border-amber-100 px-2.5 py-1 rounded-full font-medium">
                        ★ {{ request('rating_min') }}+<a href="{{ route('properties.index', request()->except('rating_min')) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endif
                    @foreach((array) request('amenities', []) as $am)
                    <span class="inline-flex items-center gap-1 text-xs bg-green-50 text-green-700 border border-green-100 px-2.5 py-1 rounded-full font-medium">
                        {{ $amenitiesList[$am]['label'] ?? $am }}<a href="{{ route('properties.index', array_merge(request()->all(), ['amenities' => array_values(array_filter((array)request('amenities',[]), fn($a) => $a !== $am))])) }}" class="hover:text-red-500 ml-0.5">×</a>
                    </span>
                    @endforeach
                    <a href="{{ route('properties.index') }}" class="text-xs text-red-500 hover:text-red-600 font-medium flex items-center gap-1 px-2">Limpiar todos</a>
                </div>
                @endif

                {{-- Results grid --}}
                @if($propiedades->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($propiedades as $propiedad)
                    @include('components.propiedad-card', compact('propiedad'))
                    @endforeach
                </div>
                <div class="mt-10">{{ $propiedades->links() }}</div>
                @else
                <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="text-6xl mb-4">&#127968;</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No encontramos propiedades</h3>
                    <p class="text-gray-500 mb-6">Intenta con otros filtros.</p>
                    <a href="{{ route('properties.index') }}" class="bg-indigo-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-colors">Ver todas las propiedades</a>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/pages/propiedades-index.js'])
@endpush

@endsection
