@extends('layouts.main')
@section('title', 'Editar Propiedad')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-start justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Editar Propiedad</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $propiedad->name }}</p>
        </div>
        @if($propiedad->status !== 'pending')
        <form action="{{ route('owner.properties.toggle', $propiedad) }}" method="POST" class="shrink-0">
            @csrf @method('PATCH')
            @if($propiedad->status === 'active')
            <button type="submit"
                    class="flex items-center gap-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Desactivar
            </button>
            @else
            <button type="submit"
                    class="flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Activar
            </button>
            @endif
        </form>
        @else
        <span class="shrink-0 text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200 px-3 py-2 rounded-xl">
            En revisión — no se puede activar/desactivar
        </span>
        @endif
    </div>

    <form id="edit-form" action="{{ route('owner.properties.update', $propiedad) }}" method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
        @csrf @method('PUT')

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Información Básica</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipo de propiedad *</label>
                    <select name="type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">Seleccionar tipo...</option>
                        @foreach($typesList as $value => $label)
                        <option value="{{ $value }}" {{ old('type', $propiedad->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                        @if($propiedad->type && !array_key_exists($propiedad->type, $typesList))
                        <option value="{{ $propiedad->type }}" selected>{{ $propiedad->type }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $propiedad->name) }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción corta</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $propiedad->short_description) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción completa *</label>
                    <textarea name="description" rows="5" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('description', $propiedad->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Ubicación</h2>
            <div class="space-y-4">
                <div x-data="{ open: false }">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Enlace de Google Maps
                        <button type="button" @click="open = !open" class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors align-middle text-xs font-bold leading-none">
                            ?
                        </button>
                    </label>
                    <input type="url" name="map_url" value="{{ old('map_url', $propiedad->map_url) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                        placeholder="https://maps.app.goo.gl/...">
                    @error('map_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                    <div x-show="open" x-transition class="mt-3 p-4 bg-indigo-50 border border-indigo-100 rounded-xl text-sm text-gray-700 space-y-2">
                        <p class="font-semibold text-indigo-700">¿Cómo obtener el enlace de Google Maps?</p>
                        <ol class="list-decimal list-inside space-y-1 text-gray-600">
                            <li>Abrí <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="font-medium text-indigo-600 underline hover:text-indigo-800">Google Maps</a> .</li>
                            <li>Buscá la dirección exacta de tu propiedad.</li>
                            <li>Una véz que la encuentres selecciona <span class="font-medium">"Compartir"</span> → <span class="font-medium">"Busca "Copiar vínculo" o "Copiar enlace""</span>.</li>
                            <li>Pegá ese enlace en el campo de arriba.</li>
                        </ol>

                    </div>
                </div>

                {{-- Dirección con autocomplete en cascada --}}
                @php
                    $currentStateName = old('state', $propiedad->state);
                    $currentProvinceId = $provinces->firstWhere('name', $currentStateName)?->id ?? '';
                @endphp
                <div x-data="addressAutocomplete('{{ $currentProvinceId }}', '{{ old('partido', $propiedad->partido) }}', '{{ old('locality', $propiedad->locality) }}')"
                    x-init="init()"
                    class="space-y-4">

                    {{-- Fila 1: Provincia → Partido → Localidad --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Provincia (select) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Provincia *</label>
                            <input type="hidden" name="state" :value="provinceName">
                            <select required @change="onProvinceChange($event)"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="">Seleccionar provincia...</option>
                                @foreach($provinces as $prov)
                                <option value="{{ $prov->id }}" data-name="{{ $prov->name }}" {{ $currentProvinceId == $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                                @endforeach
                            </select>
                            @error('state')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Partido --}}
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Partido / Departamento *</label>
                            <input type="text" name="partido" x-model="partidoInput" required autocomplete="off"
                                :disabled="!provinceId" @input="onPartidoInput" @focus="onPartidoInput" @blur="setTimeout(() => showPartidoDD = false, 150)"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none disabled:bg-gray-50 disabled:text-gray-400"
                                placeholder="Ej: Pilar">
                            @error('partido')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            <div x-show="loadingPartidos" class="absolute right-3 top-9">
                                <svg class="animate-spin w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            </div>
                            <ul x-show="showPartidoDD && partidoFiltered.length"
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto text-sm">
                                <template x-for="p in partidoFiltered" :key="p.id">
                                    <li @mousedown.prevent="selectPartido(p)"
                                        class="px-4 py-2.5 cursor-pointer hover:bg-indigo-50 hover:text-indigo-700" x-text="p.name"></li>
                                </template>
                            </ul>
                        </div>

                        {{-- Localidad --}}
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Localidad *</label>
                            <input type="text" name="locality" x-model="localidadInput" required autocomplete="off"
                                :disabled="!partidoId" @input="onLocalidadInput" @focus="onLocalidadInput" @blur="setTimeout(() => showLocalidadDD = false, 150)"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none disabled:bg-gray-50 disabled:text-gray-400"
                                placeholder="Ej: Villa Rosa">
                            @error('locality')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            <div x-show="loadingLocs" class="absolute right-3 top-9">
                                <svg class="animate-spin w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            </div>
                            <ul x-show="showLocalidadDD && localidadFiltered.length"
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto text-sm">
                                <template x-for="l in localidadFiltered" :key="l">
                                    <li @mousedown.prevent="selectLocalidad(l)"
                                        class="px-4 py-2.5 cursor-pointer hover:bg-indigo-50 hover:text-indigo-700" x-text="l"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    {{-- Fila 2: Calle + Número --}}
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Calle *</label>
                            <input type="text" name="street_name" value="{{ old('street_name', $propiedad->street_name) }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Calle">
                            @error('street_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:w-32">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Número *</label>
                            <input type="text" name="street_number" value="{{ old('street_number', $propiedad->street_number) }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="1234">
                            @error('street_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <input type="hidden" name="country" value="Argentina">
                </div>

            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Precio y Capacidad</h2>
            {{-- Fila de precios --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Precio/hora (ARS) *</label>
                    <input type="number" name="price_per_hour" value="{{ old('price_per_hour', $propiedad->price_per_hour ?? '') }}" required min="1" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('price_per_hour')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Precio/día (ARS) *</label>
                    <input type="number" name="price_per_day" value="{{ old('price_per_day', $propiedad->price_per_day ?? '') }}" required min="1" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('price_per_day')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Resto de campos --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Capacidad *</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $propiedad->capacity) }}" required min="1"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Habitaciones *</label>
                    <input type="number" name="bedrooms" value="{{ old('bedrooms', $propiedad->bedrooms) }}" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Baños *</label>
                    <input type="number" name="bathrooms" value="{{ old('bathrooms', $propiedad->bathrooms) }}" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estacionamientos *</label>
                    <input type="number" name="parking_spots" value="{{ old('parking_spots', $propiedad->parking_spots) }}" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estadía mínima (días)</label>
                    <input type="number" name="min_days" value="{{ old('min_days', $propiedad->min_days ?? 1) }}" min="1"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estadía máxima (días)</label>
                    <input type="number" name="max_days" value="{{ old('max_days', $propiedad->max_days) }}" min="1"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Sin límite">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Disponible desde (hora)</label>
                    <input type="time" name="available_from" value="{{ old('available_from', $propiedad->available_from) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Disponible hasta (hora)</label>
                    <input type="time" name="available_to" value="{{ old('available_to', $propiedad->available_to) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            {{-- Descuentos por cantidad de días --}}
            @php
                $existingDiscounts = old('day_discounts', $propiedad->day_discounts ?? [['days'=>'','discount'=>'']]);
                if (empty($existingDiscounts)) $existingDiscounts = [['days'=>'','discount'=>'']];
            @endphp
            <div class="mt-6 border-t border-gray-100 pt-5"
                 x-data="{
                    discounts: {{ json_encode($existingDiscounts) }},
                    addRow() { this.discounts.push({days:'',discount:''}) },
                    removeRow(i) { if(this.discounts.length > 1) this.discounts.splice(i,1) }
                 }">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-start gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 flex items-center gap-1.5">
                                Descuentos por cantidad de días
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">Se aplica el mayor descuento que corresponda según la cantidad de días.</p>
                        </div>
                    </div>
                    <button type="button" @click="addRow()"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 px-3 py-1.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                        + Agregar tramo
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(row, i) in discounts" :key="i">
                        <div class="flex flex-wrap items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="flex flex-wrap items-end gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">A partir de (días)</label>
                                    <input type="number" :name="`day_discounts[${i}][days]`" x-model="row.days"
                                        min="1" placeholder="Ej: 7"
                                        class="w-32 px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-xs text-gray-500 whitespace-nowrap">Descuento (%):</label>
                                    <input type="number" :name="`day_discounts[${i}][discount]`" x-model="row.discount"
                                        min="1" max="99" step="0.5" placeholder="10"
                                        class="w-20 px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                            </div>
                            <button type="button" @click="removeRow(i)" x-show="discounts.length > 1"
                                class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-100 transition-colors ml-auto" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                @error('day_discounts')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- Descuentos por fechas especiales --}}
            @php $existingDateDisc = old('date_discounts', $propiedad->date_discounts ?? []); @endphp
            <div class="mt-6 border-t border-gray-100 pt-5"
                 x-data="{
                    rows: {{ json_encode($existingDateDisc) }},
                    addRow() { this.rows.push({date_from:'',date_to:'',discount:''}) },
                    removeRow(i) { this.rows.splice(i,1) }
                 }">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-700">Descuentos por fechas especiales</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Ej: temporada baja, fiestas.  </p>
                    </div>
                    <button type="button" @click="addRow()"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 px-3 py-1.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                        + Agregar fecha
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex flex-wrap items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="flex flex-wrap items-end gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                                    <input type="date" :name="`date_discounts[${i}][date_from]`" x-model="row.date_from"
                                        class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                                    <input type="date" :name="`date_discounts[${i}][date_to]`" x-model="row.date_to"
                                        class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-xs text-gray-500 whitespace-nowrap">Descuento (%):</label>
                                    <input type="number" :name="`date_discounts[${i}][discount]`" x-model="row.discount"
                                        min="1" max="99" step="0.5" placeholder="15"
                                        class="w-20 px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                            </div>
                            <button type="button" @click="removeRow(i)"
                                class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-100 transition-colors ml-auto" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p x-show="rows.length === 0" class="text-xs text-gray-400 italic">Sin descuentos por fecha configurados.</p>
                @error('date_discounts')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- Descuentos por día de la semana --}}
            @php
                $diasSemana = [1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',0=>'Dom'];
                $existingWeekDisc = old('weekday_discounts', $propiedad->weekday_discounts ?? []);
            @endphp
            <div class="mt-6 border-t border-gray-100 pt-5"
                 x-data="{
                    rows: {{ json_encode($existingWeekDisc) }},
                    addRow() { this.rows.push({days:[],discount:''}) },
                    removeRow(i) { this.rows.splice(i,1) },
                    toggleDay(row, d) { const idx=row.days.indexOf(d); idx===-1 ? row.days.push(d) : row.days.splice(idx,1) },
                    hasDay(row, d) { return row.days.indexOf(d) !== -1 }
                 }">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-700">Descuentos por día de la semana</h3>
                    </div>
                    <button type="button" @click="addRow()"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 px-3 py-1.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                        + Agregar descuento
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex flex-wrap items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="flex flex-wrap gap-1">
                                @foreach($diasSemana as $dv => $dl)
                                <button type="button"
                                    @click="toggleDay(row, {{ $dv }})"
                                    :class="hasDay(row, {{ $dv }}) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300'"
                                    class="px-2.5 py-1 text-xs font-semibold border rounded-lg transition-colors select-none">
                                    {{ $dl }}
                                </button>
                                @endforeach
                            </div>
                            @foreach($diasSemana as $dv => $dl)
                            <template x-if="hasDay(row, {{ $dv }})">
                                <input type="hidden" :name="`weekday_discounts[${i}][days][]`" value="{{ $dv }}">
                            </template>
                            @endforeach
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-500 whitespace-nowrap">Descuento (%):</label>
                                <input type="number" :name="`weekday_discounts[${i}][discount]`" x-model="row.discount"
                                    min="1" max="99" step="0.5" placeholder="10"
                                    class="w-20 px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <button type="button" @click="removeRow(i)"
                                class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-100 transition-colors ml-auto" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p x-show="rows.length === 0" class="text-xs text-gray-400 italic">Sin descuentos por día de semana configurados.</p>
                @error('weekday_discounts')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Comodidades</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
                @foreach($amenitiesList as $key => $amenity)
                <label class="flex items-center gap-2.5 cursor-pointer p-3 rounded-xl hover:bg-indigo-50 transition-colors">
                    <input type="checkbox" name="amenities[]" value="{{ $key }}"
                           {{ in_array($key, old('amenities', $propiedad->amenities ?? [])) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-indigo-600">
                    <span class="text-lg">{{ $amenity['icon'] }}</span>
                    <span class="text-sm text-gray-700">{{ $amenity['label'] }}</span>
                </label>
                @endforeach
            </div>

            {{-- Custom amenities --}}
            @php
                $existingAmenities = old('amenities', $propiedad->amenities ?? []);
                $customExisting = array_values(array_filter($existingAmenities, fn($a) => !array_key_exists($a, $amenitiesList)));
            @endphp
            <div x-data="customAmenities({{ json_encode($customExisting) }})">
                <p class="text-sm font-semibold text-gray-700 mb-2">Agregar comodidad personalizada</p>
                <div class="flex flex-wrap gap-2 mb-3" x-show="items.length > 0" x-cloak>
                    <template x-for="(item, i) in items" :key="i">
                        <span class="flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm px-3 py-1.5 rounded-lg">
                            <input type="hidden" name="amenities[]" :value="item">
                            <span x-text="item"></span>
                            <button type="button" @click="remove(i)" class="text-indigo-400 hover:text-indigo-600 transition-colors ml-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input x-model="input" @keydown.enter.prevent="add()" type="text"
                        placeholder="Ej: Cancha de padel, Sauna, Quincho techado..."
                        class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <button type="button" @click="add()"
                        class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 text-indigo-700 font-semibold text-sm rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Agregar
                    </button>
                </div>
            </div>
        </div>

         <x-property-services-form :existing-services="$propiedad->services" />

        {{-- Reglas --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Reglas del espacio (opcional)</h2>
            <div class="flex gap-2 mb-4">
                <input type="text" id="regla-input"
                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                    placeholder="Ej: No se permite música alta">
                <button type="button" onclick="addRegla()"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Agregar
                </button>
            </div>
            <div id="reglas-list" class="flex flex-wrap gap-2"></div>
            <textarea id="rules-hidden" name="rules" class="hidden"></textarea>
        </div>

        {{-- Fotos --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Fotos</h2>
                    <p class="text-xs text-gray-400 mt-0.5"></p>
                </div>
            </div>

            {{-- Unified photo grid --}}
            <div id="photos-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                {{-- Existing photos --}}
                @foreach($propiedad->images as $image)
                <div id="photo-{{ $image->id }}" class="pcard" data-id="{{ $image->id }}">
                    <img src="{{ $image->url }}" alt="" draggable="false">
                    <button type="button" class="pcard-del" onclick="toggleDelete(this)" title="Marcar para eliminar">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    @if($loop->first)
                    <span class="pcard-badge" style="background:rgba(99,102,241,.85)">⭐ Portada</span>
                    @endif
                </div>
                @endforeach

                {{-- Add button (always last, JS inserts new cards before it) --}}
                <label id="photo-add-btn" class="pcard-add" title="Agregar fotos">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar fotos</span>
                    <input type="file" id="photo-file-input" name="images[]" multiple accept="image/*" class="hidden">
                </label>

            </div>

            {{-- Drop zone hint (shows when dragging) --}}
            <div id="drag-overlay">
                <svg width="48" height="48" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p>Soltá las fotos aquí</p>
            </div>

            <input type="file" id="photos-hidden-input" name="images[]" multiple accept="image/*" class="hidden">
        </div>



        <div class="flex gap-4">
            <button type="button" onclick="submitEdit()" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-2xl transition-all">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

@endsection

@push('styles')
@vite(['resources/css/pages/propiedades-edit.css'])
@endpush

@push('scripts')
@php
    $rulesRaw = old('rules', $propiedad->rules ?? []);
    $rulesArr = is_array($rulesRaw) ? $rulesRaw : array_filter(explode("\n", (string) $rulesRaw));
@endphp
<script>
window.EDIT_REGLAS = {!! json_encode(array_values(array_filter(array_map('trim', $rulesArr)))) !!};
</script>
@vite(['resources/js/pages/propiedades-edit.js'])
@endpush
