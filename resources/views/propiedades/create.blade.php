@extends('layouts.main')
@section('title', 'Publicar Propiedad')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Publicar mi Propiedad</h1>
        <p class="text-gray-500 text-sm mt-1">Completa la información de tu espacio y publicala al instante.</p>
    </div>

    <form id="create-form" action="{{ route('owner.properties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
        @csrf

        {{-- Basic info --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Información Básica</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipo de propiedad *</label>
                    <select name="type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">Seleccionar tipo...</option>
                        @foreach(\App\Models\Property::typesList() as $value => $label)
                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre de la propiedad *</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ej: Quinta Los Pinos">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripcion corta</label>
                    <input type="text" name="short_description" value="{{ old('short_description') }}" maxlength="500"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Un resumen de tu propiedad (aparece en las cards)">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripcion completa *</label>
                    <textarea name="description" rows="5"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none" placeholder="Describe tu propiedad, que la hace especial, el ambiente, los alrededores...">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Ubicación</h2>
            <div class="space-y-4">

                {{-- Google Maps --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Enlace de Google Maps
                        <button type="button" onclick="document.getElementById('maps-help').classList.toggle('hidden')"
                            class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors align-middle text-xs font-bold leading-none">?</button>
                    </label>
                    <input type="url" name="map_url" value="{{ old('map_url') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                        placeholder="https://maps.app.goo.gl/...">
                    @error('map_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <div id="maps-help" class="hidden mt-3 p-4 bg-indigo-50 border border-indigo-100 rounded-xl text-sm text-gray-700 space-y-2">
                        <p class="font-semibold text-indigo-700">¿Cómo obtener el enlace de Google Maps?</p>
                        <ol class="list-decimal list-inside space-y-1 text-gray-600">
                            <li>Abrí <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="font-medium text-indigo-600 underline hover:text-indigo-800">Google Maps</a>.</li>
                            <li>Buscá la dirección exacta de tu propiedad.</li>
                            <li>Seleccioná <span class="font-medium">"Compartir"</span> → <span class="font-medium">"Busca "Copiar vínculo" o "Copiar enlace""</span>.</li>
                            <li>Pegá ese enlace en el campo de arriba.</li>
                        </ol>
                    </div>
                </div>

                {{-- Dirección --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Provincia *</label>
                        <select id="sel-province"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="">Seleccionar provincia...</option>
                            @foreach($provinces as $prov)
                            <option value="{{ $prov->id }}" data-name="{{ $prov->name }}" {{ old('state') == $prov->name ? 'selected' : '' }}>{{ $prov->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" id="inp-state" name="state" value="{{ old('state') }}">
                        @error('state')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Partido / Departamento *</label>
                        <input type="text" id="input-partido" name="partido" value="{{ old('partido') }}" autocomplete="off"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none disabled:bg-gray-50 disabled:text-gray-400"
                            placeholder="Escriba..." disabled>
                        <div id="spin-partido" class="hidden absolute right-3 top-9">
                            <svg class="animate-spin w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        </div>
                        <ul id="dd-partido" class="hidden absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto text-sm"></ul>
                        @error('partido')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Localidad *</label>
                        <input type="text" id="input-localidad" name="locality" value="{{ old('locality') }}" autocomplete="off"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none disabled:bg-gray-50 disabled:text-gray-400"
                            placeholder="Escriba..." disabled>
                        <div id="spin-localidad" class="hidden absolute right-3 top-9">
                            <svg class="animate-spin w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        </div>
                        <ul id="dd-localidad" class="hidden absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-52 overflow-y-auto text-sm"></ul>
                        @error('locality')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Calle *</label>
                        <input type="text" name="street_name" value="{{ old('street_name') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Calle">
                        @error('street_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:w-32">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Número *</label>
                        <input type="text" name="street_number" value="{{ old('street_number') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="1234">
                        @error('street_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <input type="hidden" name="country" value="Argentina">
            </div>
        </div>

        {{-- Pricing & Capacity --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Precio y Capacidad</h2>
            {{-- Fila de precios --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Precio por hora (ARS) *</label>
                    <input type="number" id="inp-hora" name="price_per_hour" value="{{ old('price_per_hour') }}" min="1" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0">
                    @error('price_per_hour')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Precio por día (ARS) *</label>
                    <input type="number" id="inp-dia" name="price_per_day" value="{{ old('price_per_day') }}" min="1" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0">
                    @error('price_per_day')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Resto de campos --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Capacidad maxima *</label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}" min="1"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Habitaciones *</label>
                    <input type="number" name="bedrooms" value="{{ old('bedrooms') }}" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Baños *</label>
                    <input type="number" name="bathrooms" value="{{ old('bathrooms') }}" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estacionamientos *</label>
                    <input type="number" name="parking_spots" value="{{ old('parking_spots') }}" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estadía mínima (días)</label>
                    <input type="number" name="min_days" value="{{ old('min_days', 1) }}" min="1"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estadía máxima (días)</label>
                    <input type="number" name="max_days" value="{{ old('max_days') }}" min="1"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Sin límite">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Disponible desde (hora)</label>
                    <input type="time" name="available_from" value="{{ old('available_from') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Disponible hasta (hora)</label>
                    <input type="time" name="available_to" value="{{ old('available_to') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            {{-- Descuentos por cantidad de días --}}
            <div class="mt-6 border-t border-gray-100 pt-5"
                 x-data="{
                    discounts: {{ json_encode(old('day_discounts', [['days'=>'','discount'=>'']])) }},
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
            <div class="mt-6 border-t border-gray-100 pt-5"
                 x-data="{
                    rows: {{ json_encode(old('date_discounts', [])) }},
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
            @php $diasSemana = [1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',0=>'Dom']; @endphp
            <div class="mt-6 border-t border-gray-100 pt-5"
                 x-data="{
                    rows: {{ json_encode(old('weekday_discounts', [])) }},
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
                            {{-- Botones de días --}}
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
                            {{-- Inputs hidden para días seleccionados --}}
                            @foreach($diasSemana as $dv => $dl)
                            <template x-if="hasDay(row, {{ $dv }})">
                                <input type="hidden" :name="`weekday_discounts[${i}][days][]`" value="{{ $dv }}">
                            </template>
                            @endforeach
                            {{-- Descuento --}}
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

        {{-- Amenities --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Comodidades</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
                @foreach($amenitiesList as $key => $amenity)
                <label class="flex items-center gap-2.5 cursor-pointer p-3 rounded-xl hover:bg-indigo-50 transition-colors group">
                    <input type="checkbox" name="amenities[]" value="{{ $key }}"
                           {{ in_array($key, old('amenities', [])) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-indigo-600">
                    <span class="text-lg">{{ $amenity['icon'] }}</span>
                    <span class="text-sm text-gray-700 group-hover:text-indigo-700">{{ $amenity['label'] }}</span>
                </label>
                @endforeach
            </div>
            <p class="text-sm font-semibold text-gray-700 mb-2">Agregar comodidad personalizada</p>
            <div id="custom-amenities-list" class="flex flex-wrap gap-2 mb-3"></div>
            <div class="flex gap-2">
                <input type="text" id="custom-amenity-input" placeholder="Ej: Cancha de padel, Sauna..."
                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <button type="button" onclick="addAmenity()"
                    class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 text-indigo-700 font-semibold text-sm rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar
                </button>
            </div>
        </div>

        <x-property-services-form />

        {{-- Rules --}}
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

        {{-- Images --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-900">Fotos de la propiedad</h2>
                <p class="text-xs text-gray-400 mt-0.5">La primera foto sera la imagen principal. Max. 5MB por imagen.</p>
            </div>
            <div id="photos-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <label id="photo-add-btn" class="pcard-add" title="Agregar fotos">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar fotos</span>
                    <input type="file" id="photo-file-input" multiple accept="image/*" class="hidden">
                </label>
            </div>
            <div id="drag-overlay">
                <svg width="48" height="48" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p>Soltá las fotos aquí</p>
            </div>
            <input type="file" id="photos-hidden-input" name="images[]" multiple accept="image/*" class="hidden">
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
            @foreach($errors->all() as $error)
            <p class="text-red-600 text-sm">• {{ $error }}</p>
            @endforeach
        </div>
        @endif



        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-2xl transition-all shadow-lg shadow-indigo-200">
                Publicar Propiedad
            </button>
        </div>
    </form>
</div>

@endsection

@push('styles')
<style>
.pcard { position:relative; aspect-ratio:4/3; border-radius:12px; overflow:hidden; background:#1f2937; transition:opacity .2s; }
.pcard img { max-width:100%; max-height:100%; width:auto; height:auto; display:block; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); }
.pcard-del { position:absolute; top:7px; right:7px; width:28px; height:28px; background:rgba(239,68,68,0.88); border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#fff; transition:background .15s; box-shadow:0 2px 6px rgba(0,0,0,.35); z-index:2; }
.pcard-del:hover { background:#dc2626; }
.pcard-badge { position:absolute; bottom:6px; left:6px; font-size:10px; color:#fff; padding:2px 8px; border-radius:999px; pointer-events:none; z-index:2; }
.pcard-add { aspect-ratio:4/3; border-radius:12px; border:2px dashed #d1d5db; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; cursor:pointer; color:#9ca3af; transition:border-color .15s,background .15s,color .15s; font-size:13px; font-weight:500; }
.pcard-add:hover { border-color:#6366f1; background:#eef2ff; color:#6366f1; }
#drag-overlay { display:none; position:fixed; inset:0; z-index:9999; background:rgba(99,102,241,.3); backdrop-filter:blur(3px); align-items:center; justify-content:center; flex-direction:column; gap:12px; outline:4px dashed #6366f1; outline-offset:-12px; color:#fff; font-size:22px; font-weight:700; pointer-events:none; }
#drag-overlay.active { display:flex; }
.inp-err { border:2px solid #ef4444 !important; }
.ts-wrapper.inp-err .ts-control { border:2px solid #ef4444 !important; border-radius:0.75rem; }
.err-msg { display:block; color:#ef4444; font-size:0.75rem; margin-top:4px; font-weight:500; }
.dd-item { padding:10px 16px; cursor:pointer; }
.dd-item:hover { background:#eef2ff; color:#4f46e5; }
</style>
@endpush

@push('scripts')
<script>
// ===================== AUTOCOMPLETE PARTIDO / LOCALIDAD =====================
var partidoOptions = [];
var partidoId = null;

document.getElementById('sel-province').addEventListener('change', function() {
    var provinceId = this.value;
    var provinceName = this.options[this.selectedIndex] ? (this.options[this.selectedIndex].dataset.name || '') : '';
    document.getElementById('inp-state').value = provinceName;
    var inpP = document.getElementById('input-partido');
    var inpL = document.getElementById('input-localidad');
    inpP.value = ''; inpP.disabled = !provinceId;
    inpL.value = ''; inpL.disabled = true;
    partidoOptions = []; partidoId = null;
    document.getElementById('dd-partido').innerHTML = '';
    document.getElementById('dd-localidad').innerHTML = '';
    if (!provinceId) return;
    document.getElementById('spin-partido').classList.remove('hidden');
    fetch('/geo/partidos?province_id=' + encodeURIComponent(provinceId))
        .then(function(r){ return r.json(); })
        .then(function(data){
            partidoOptions = data;
            document.getElementById('spin-partido').classList.add('hidden');
        });
});

document.getElementById('input-partido').addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    var dd = document.getElementById('dd-partido');
    dd.innerHTML = '';
    if (!q) { dd.classList.add('hidden'); return; }
    var filtered = partidoOptions.filter(function(p){ return p.name.toLowerCase().includes(q); }).slice(0, 20);
    if (!filtered.length) { dd.classList.add('hidden'); return; }
    filtered.forEach(function(p) {
        var li = document.createElement('li');
        li.className = 'dd-item';
        li.textContent = p.name;
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById('input-partido').value = p.name;
            partidoId = p.id;
            dd.classList.add('hidden');
            cargarLocalidades(p.id);
        });
        dd.appendChild(li);
    });
    dd.classList.remove('hidden');
});

document.getElementById('input-partido').addEventListener('blur', function() {
    setTimeout(function(){ document.getElementById('dd-partido').classList.add('hidden'); }, 150);
});

function cargarLocalidades(pid) {
    var inpL = document.getElementById('input-localidad');
    inpL.value = ''; inpL.disabled = true;
    document.getElementById('spin-localidad').classList.remove('hidden');
    fetch('/geo/localidades?partido_id=' + pid)
        .then(function(r){ return r.json(); })
        .then(function(data){
            inpL._localidades = data;
            inpL.disabled = false;
            document.getElementById('spin-localidad').classList.add('hidden');
        });
}

document.getElementById('input-localidad').addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    var dd = document.getElementById('dd-localidad');
    dd.innerHTML = '';
    var locs = this._localidades || [];
    if (!q) { dd.classList.add('hidden'); return; }
    var filtered = locs.filter(function(l){ return l.toLowerCase().includes(q); }).slice(0, 20);
    if (!filtered.length) { dd.classList.add('hidden'); return; }
    filtered.forEach(function(l) {
        var li = document.createElement('li');
        li.className = 'dd-item';
        li.textContent = l;
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById('input-localidad').value = l;
            dd.classList.add('hidden');
        });
        dd.appendChild(li);
    });
    dd.classList.remove('hidden');
});

document.getElementById('input-localidad').addEventListener('blur', function() {
    setTimeout(function(){ document.getElementById('dd-localidad').classList.add('hidden'); }, 150);
});

// Init con old() values
(function() {
    var prov = document.getElementById('sel-province').value;
    var oldPartido = '{{ old('partido') }}';
    var oldLocality = '{{ old('locality') }}';
    if (!prov) return;
    fetch('/geo/partidos?province_id=' + encodeURIComponent(prov))
        .then(function(r){ return r.json(); })
        .then(function(data){
            partidoOptions = data;
            if (oldPartido) {
                document.getElementById('input-partido').disabled = false;
                var match = data.find(function(p){ return p.name === oldPartido; });
                if (match) {
                    partidoId = match.id;
                    cargarLocalidades(match.id);
                    if (oldLocality) {
                        setTimeout(function(){
                            document.getElementById('input-localidad').value = oldLocality;
                        }, 600);
                    }
                }
            }
        });
})();

// ===================== REGLAS =====================
var reglas = [];
@if(old('rules'))
reglas = {!! json_encode(array_values(array_filter(array_map('trim', explode("\n", old('rules')))))) !!};
renderReglas();
@endif

document.getElementById('regla-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); addRegla(); }
});

function addRegla() {
    var val = document.getElementById('regla-input').value.trim();
    if (!val || reglas.includes(val)) return;
    reglas.push(val);
    document.getElementById('regla-input').value = '';
    renderReglas();
}

function removeRegla(i) {
    reglas.splice(i, 1);
    renderReglas();
}

function renderReglas() {
    var list = document.getElementById('reglas-list');
    list.innerHTML = '';
    reglas.forEach(function(r, i) {
        var span = document.createElement('span');
        span.className = 'inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-sm font-medium px-3 py-1.5 rounded-full';
        span.innerHTML = '<span>' + r.replace(/</g,'&lt;') + '</span><button type="button" onclick="removeRegla(' + i + ')" class="text-indigo-400 hover:text-indigo-700 leading-none">&times;</button>';
        list.appendChild(span);
    });
    document.getElementById('rules-hidden').value = reglas.join('\n');
}

// ===================== COMODIDADES CUSTOM =====================
document.getElementById('custom-amenity-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); addAmenity(); }
});

function addAmenity() {
    var val = document.getElementById('custom-amenity-input').value.trim();
    if (!val) return;
    var list = document.getElementById('custom-amenities-list');
    var span = document.createElement('span');
    span.className = 'flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm px-3 py-1.5 rounded-lg';
    span.innerHTML = '<input type="hidden" name="amenities[]" value="' + val.replace(/"/g,'&quot;') + '"><span>' + val.replace(/</g,'&lt;') + '</span><button type="button" onclick="this.parentElement.remove()" class="text-indigo-400 hover:text-indigo-600 ml-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>';
    list.appendChild(span);
    document.getElementById('custom-amenity-input').value = '';
}

// ===================== FOTOS =====================
var newFiles = [];

document.getElementById('photo-file-input').addEventListener('change', function() {
    addPhotos(Array.from(this.files));
    this.value = '';
});

var dragCounter = 0;
window.addEventListener('dragenter', function(e) {
    if (!e.dataTransfer.types.includes('Files')) return;
    dragCounter++;
    document.getElementById('drag-overlay').classList.add('active');
});
window.addEventListener('dragleave', function() {
    if (--dragCounter <= 0) { dragCounter = 0; document.getElementById('drag-overlay').classList.remove('active'); }
});
window.addEventListener('dragover', function(e){ e.preventDefault(); });
window.addEventListener('drop', function(e) {
    e.preventDefault(); dragCounter = 0;
    document.getElementById('drag-overlay').classList.remove('active');
    var files = Array.from(e.dataTransfer.files).filter(function(f){ return f.type.startsWith('image/'); });
    if (files.length) addPhotos(files);
});

function addPhotos(files) {
    newFiles = newFiles.concat(files);
    renderPhotos();
}

function removeNew(i) {
    newFiles.splice(i, 1);
    renderPhotos();
}

function renderPhotos() {
    document.querySelectorAll('.pcard.new').forEach(function(el){ el.remove(); });
    var addBtn = document.getElementById('photo-add-btn');
    newFiles.forEach(function(file, i) {
        var url = URL.createObjectURL(file);
        var card = document.createElement('div');
        card.className = 'pcard new';
        card.innerHTML = '<img src="' + url + '" alt="" draggable="false">'
            + '<button type="button" class="pcard-del" onclick="removeNew(' + i + ')">'
            + '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>'
            + (i === 0 ? '<span class="pcard-badge" style="background:rgba(99,102,241,.85)">⭐ Portada</span>' : '<span class="pcard-badge" style="background:rgba(99,102,241,.85)">Nueva</span>');
        addBtn.parentNode.insertBefore(card, addBtn);
    });
}

// ===================== VALIDACION + SUBMIT =====================
function setErr(el, msg) {
    console.log('[setErr] tag:', el.tagName, '| name:', el.getAttribute('name'), '| classes antes:', el.className);
    el.classList.add('inp-err');
    console.log('[setErr] classes despues:', el.className);
    var container = el.parentElement;
    if (container && !container.querySelector('.err-msg')) {
        var sp = document.createElement('span');
        sp.className = 'err-msg';
        sp.textContent = msg;
        container.appendChild(sp);
    }
}

function clearErr(el) {
    el.classList.remove('inp-err');
    // TomSelect: el mensaje está como siguiente hermano del wrapper
    if (el.classList.contains('ts-wrapper')) {
        if (el.nextElementSibling && el.nextElementSibling.classList.contains('err-msg')) {
            el.nextElementSibling.remove();
        }
        return;
    }
    // Inputs/selects normales: el mensaje está en el contenedor padre
    var container = el.parentElement;
    if (container) {
        var sp = container.querySelector('.err-msg');
        if (sp) sp.remove();
    }
}

document.getElementById('create-form').addEventListener('input', function(e) {
    if (e.target.classList.contains('inp-err')) clearErr(e.target);
});
document.getElementById('create-form').addEventListener('change', function(e) {
    if (e.target.classList.contains('inp-err')) clearErr(e.target);
    // limpiar Tom Select wrapper
    var wrap = e.target.closest ? e.target.closest('.ts-wrapper') : null;
    if (wrap) clearErr(wrap);
});

/**
 * Valida los campos obligatorios del formulario.
 * Retorna true si todo es válido; false si hay errores (marca los campos en rojo).
 */
function validateForm() {
    document.querySelectorAll('.inp-err').forEach(function(el) { clearErr(el); });

    var form = document.getElementById('create-form');
    var errores = [];

    function req(name, msg) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) { console.warn('[req] campo no encontrado:', name); return; }
        var v = (el.value || '').trim();
        var invalid = !v || (el.type === 'number' && (isNaN(parseFloat(v)) || parseFloat(v) < 1));
        console.log('[req]', name, '| value:', JSON.stringify(el.value), '| disabled:', el.disabled, '| invalid:', invalid);
        if (invalid) { setErr(el, msg); errores.push(el); }
    }

    // Para campos numéricos donde 0 es válido: solo valida que no esté vacío
    function reqNum(name, msg) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        if (el.value === '' || el.value === null || el.value === undefined) {
            setErr(el, msg); errores.push(el);
        }
    }

    req('type',           'El tipo de propiedad es obligatorio.');
    req('name',           'El nombre es obligatorio.');
    req('description',    'La descripción es obligatoria.');
    // Provincia: validar el select visible (sel-province), no el hidden
    (function() {
        var sel = document.getElementById('sel-province');
        if (!sel || !sel.value) { setErr(sel, 'Seleccioná una provincia.'); errores.push(sel); }
    })();
    req('partido',        'El partido es obligatorio.');
    req('locality',       'La localidad es obligatoria.');
    req('street_name',    'La calle es obligatoria.');
    req('street_number',  'El número es obligatorio.');
    req('capacity',       'La capacidad es obligatoria.');
    reqNum('bedrooms',     'Las habitaciones son obligatorias (podés ingresar 0).');
    reqNum('bathrooms',    'Los baños son obligatorios (podés ingresar 0).');
    reqNum('parking_spots','Los estacionamientos son obligatorios (podés ingresar 0).');
    req('price_per_hour', 'El precio por hora es obligatorio.');
    req('price_per_day',  'El precio por día es obligatorio.');

    console.log('[validateForm] campos con error:', errores.length, errores.map(function(e){ return e.getAttribute ? e.getAttribute('name') || e.className : '?'; }));

    if (errores.length > 0) {
        errores[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return true;
}

document.getElementById('create-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var valid = validateForm();
    console.log('[validateForm] resultado:', valid);
    if (!valid) return;

    if (newFiles.length) {
        var dt = new DataTransfer();
        newFiles.forEach(function(f){ dt.items.add(f); });
        document.getElementById('photos-hidden-input').files = dt.files;
    }

    this.submit();
});
</script>
@endpush
