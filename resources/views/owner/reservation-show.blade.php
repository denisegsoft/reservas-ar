@use('Illuminate\Support\Facades\Storage')
@extends('layouts.main')
@section('title', 'Gestionar Reserva #' . $reservation->id)
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('owner.reservations') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-black text-gray-900">Reserva #{{ $reservation->id }}</h1>
            <p class="text-gray-500 text-sm">{{ $reservation->property->name }}</p>
        </div>
        {{-- <a href="{{ route('owner.reservations.pdf', $reservation) }}"
           target="_blank"
           class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-xl transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Generar PDF
        </a> --}}
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <form action="{{ route('owner.reservations.update', $reservation) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        {{-- Cliente --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Cliente</h2>

            @if(!auth()->user()->hasSubscription() && !auth()->user()->isAdmin())
            {{-- Bloqueado: sin suscripción --}}
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-400">● ● ● ● ● ●</p>
                    <p class="text-sm text-gray-300 mt-0.5">● ● ● ● ● ● ● ● ● ●</p>
                    <div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl p-3">
                        <p class="text-amber-800 text-xs font-semibold mb-1">🔒 Datos bloqueados</p>
                        <p class="text-amber-700 text-xs">Activá tu suscripción para ver el nombre, teléfono y contacto de quienes te reservan.</p>
                        <a href="{{ route('subscription.payment') }}"
                           class="inline-block mt-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-1.5 rounded-xl transition-colors">
                            Activar suscripción →
                        </a>
                    </div>
                </div>
            </div>
            @else
            {{-- Datos del cliente visibles --}}
            <div class="flex items-start gap-4">
                <img src="{{ $reservation->user->avatar_url }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">{{ $reservation->user->full_name }}</p>
                    <p class="text-sm text-gray-400">{{ $reservation->user->email }}</p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                        @if($reservation->user->phone)
                        <p class="text-sm text-gray-500">
                            <span class="text-gray-400">Tel:</span> {{ $reservation->user->phone }}
                        </p>
                        @endif
                        @if($reservation->user->dni)
                        <p class="text-sm text-gray-500">
                            <span class="text-gray-400">DNI:</span> {{ $reservation->user->dni }}
                        </p>
                        @endif
                        @if($reservation->user->whatsapp_link)
                        <a href="{{ $reservation->user->whatsapp_link }}" target="_blank"
                           class="text-sm text-green-600 hover:text-green-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp
                        </a>
                        @endif
                    </div>
                </div>
                <a href="{{ route('messages.conversation', $reservation->user) }}?reservation={{ $reservation->id }}"
                   target="_blank"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex-shrink-0">
                    Enviar mensaje
                </a>
            </div>
            @endif
        </div>


        {{-- Propiedad --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Propiedad</h2>
            <a href="{{ route('owner.properties.edit', $reservation->property) }}" class="flex items-center gap-4 group">
                <img src="{{ $reservation->property->cover_image_url }}" class="w-16 h-16 object-cover rounded-xl flex-shrink-0"
                     onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&q=80'">
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $reservation->property->name }}</p>
                    <p class="text-sm text-gray-400">{{ $reservation->property->city }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 transition-colors ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        {{-- Fechas y huéspedes --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Fechas y huéspedes</h2>
                <button type="button" onclick="abrirCalendario()"
                        class="flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Ver disponibilidad
                </button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Check-in</label>
                    <input type="date" name="check_in" value="{{ $reservation->check_in->format('Y-m-d') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Check-out</label>
                    <input type="date" name="check_out" value="{{ $reservation->check_out->format('Y-m-d') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Hora entrada</label>
                    <input type="time" name="check_in_time" value="{{ $reservation->check_in_time ?? '14:00' }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Hora salida</label>
                    <input type="time" name="check_out_time" value="{{ $reservation->check_out_time ?? '11:00' }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Huéspedes</label>
                    <input type="number" name="guests" value="{{ $reservation->guests }}" min="1"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        {{-- Servicios adicionales --}}
        @if($reservation->property->services->count())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Servicios adicionales</h2>
            <x-reservation-services
                :available-services="$reservation->property->services"
                :selected-services="$reservation->services"
            />
        </div>
        @endif

        {{-- Costos adicionales --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
             x-data="{
                costs: {{ Js::from($reservation->extraCosts->map(fn($c) => ['name' => $c->name, 'price' => (float)$c->price])->values()) }},
                add() { this.costs.push({ name: '', price: '' }) },
                remove(i) { this.costs.splice(i, 1) }
             }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Costos adicionales</h2>
                <button type="button" @click="add()"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                </button>
            </div>

            <template x-if="costs.length === 0">
                <p class="text-sm text-gray-400 text-center py-3">Sin costos adicionales. Hacé clic en Agregar para añadir uno.</p>
            </template>

            <div class="space-y-2">
                <template x-for="(cost, index) in costs" :key="index">
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text"
                               :name="'extra_costs[' + index + '][name]'"
                               x-model="cost.name"
                               placeholder="Descripción"
                               class="flex-1 max-[500px]:w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                <input type="number"
                                       :name="'extra_costs[' + index + '][price]'"
                                       x-model="cost.price"
                                       placeholder="0"
                                       min="0" step="0.01"
                                       class="w-32 pl-6 pr-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <button type="button" @click="remove(index)"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Descuentos adicionales --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
             x-data="{
                discounts: {{ Js::from($reservation->discounts->map(fn($d) => ['name' => $d->name, 'price' => (float)$d->price])->values()) }},
                add() { this.discounts.push({ name: '', price: '' }) },
                remove(i) { this.discounts.splice(i, 1) }
             }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Descuentos adicionales</h2>
                <button type="button" @click="add()"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                </button>
            </div>

            <template x-if="discounts.length === 0">
                <p class="text-sm text-gray-400 text-center py-3">Sin descuentos adicionales.</p>
            </template>

            <div class="space-y-2">
                <template x-for="(discount, index) in discounts" :key="index">
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text"
                               :name="'discounts[' + index + '][name]'"
                               x-model="discount.name"
                               placeholder="Descripción del descuento"
                               class="flex-1 max-[500px]:w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none">
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">-$</span>
                                <input type="number"
                                       :name="'discounts[' + index + '][price]'"
                                       x-model="discount.price"
                                       placeholder="0"
                                       min="0" step="0.01" style="padding-left: 30px"
                                       class="w-32 pl-[38px] pr-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none">
                            </div>
                            <button type="button" @click="remove(index)"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Notas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Notas internas</h2>
            <textarea name="notes" rows="3" placeholder="Notas visibles solo para vos..."
                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('notes', $reservation->notes) }}</textarea>
        </div>

        {{-- Estado --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Estado</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Reserva</label>
                    <select name="status"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="pending"   @selected($reservation->status === 'pending')>Pendiente</option>
                        <option value="confirmed" @selected($reservation->status === 'confirmed')>Confirmada</option>
                        <option value="cancelled" @selected($reservation->status === 'cancelled')>Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Pago</label>
                    <select name="payment_status"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="unpaid"   @selected($reservation->payment_status === 'unpaid')>Pendiente</option>
                        <option value="paid"     @selected($reservation->payment_status === 'paid')>Pagado</option>
                        <option value="refunded" @selected($reservation->payment_status === 'refunded')>Reembolsado</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Medio de pago</label>
                    <select name="payment_method"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value=""       @selected(!$reservation->payment_method)>— Sin especificar —</option>
                        <option value="transfer" @selected($reservation->payment_method === 'transfer')>Transferencia</option>
                        <option value="cash"     @selected($reservation->payment_method === 'cash')>Efectivo</option>
                        <option value="credit"   @selected($reservation->payment_method === 'credit')>Crédito</option>
                    </select>
                </div>
            </div>
        </div>

        <x-reservation-price-summary :reservation="$reservation" :recalculate="true" />

        {{-- Factura --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Factura <span class="text-gray-300 font-normal normal-case">(opcional)</span></h2>

            @if($reservation->invoice_path)
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200 mb-4">
                <svg class="w-8 h-8 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ basename($reservation->invoice_path) }}</p>
                    <p class="text-xs text-gray-400">Subida el {{ $reservation->invoice_uploaded_at->format('d/m/Y H:i') }}</p>
                </div>
                <a href="{{ Storage::url($reservation->invoice_path) }}" target="_blank"
                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex-shrink-0">
                    Ver
                </a>
            </div>
            @endif

            <label class="flex items-center gap-3 cursor-pointer border border-dashed border-gray-300 hover:border-indigo-400 rounded-xl px-4 py-3.5 transition-colors">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span class="text-sm text-gray-500" id="invoice-label">
                    {{ $reservation->invoice_path ? 'Reemplazar factura' : 'Adjuntar factura (PDF, JPG, PNG — máx. 5 MB)' }}
                </span>
                <input type="file" name="invoice" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                       onchange="document.getElementById('invoice-label').textContent = this.files[0]?.name ?? ''">
            </label>
            <p class="text-xs text-gray-400 mt-2">Al guardar se le enviará un mail al cliente con la factura adjunta.</p>
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm">
            Guardar cambios
        </button>

    </form>

    {{-- Eliminar factura (form separado, fuera del form principal) --}}
    @if($reservation->invoice_path)
    <form action="{{ route('owner.reservations.invoice.delete', $reservation) }}" method="POST" class="mt-3"
          onsubmit="return confirm('¿Eliminar la factura?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full text-sm font-medium text-red-500 hover:text-red-700 py-2 transition-colors">
            Eliminar factura adjunta
        </button>
    </form>
    @endif
</div>

<x-calendar-modal />

@push('scripts')
<script>
window.RS_PROPERTY_NAME = @js($reservation->property->name);
window.RS_RESERVAS      = @json($reservasPropiedad);
</script>
@vite(['resources/js/pages/owner-reservation-show.js'])
@endpush

@endsection
