@extends('layouts.main')
@section('title', 'Crear Reserva')
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
        <div>
            <h1 class="text-2xl font-black text-gray-900">Crear reserva</h1>
            <p class="text-gray-500 text-sm">Registrá una reserva manualmente</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <form action="{{ route('owner.reservations.store') }}" method="POST" class="space-y-6" x-data="reservaForm">
        @csrf

        {{-- Propiedad --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Propiedad</h2>
            @if($propiedades->isEmpty())
                <p class="text-sm text-gray-400">No tenés propiedades activas.</p>
            @else
            <select name="property_id" required
                @change="selectedProperty = properties.find(p => p.id == $event.target.value); calcTotal()"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Seleccioná una propiedad...</option>
                @foreach($propiedades as $propiedad)
                <option value="{{ $propiedad->id }}" {{ old('property_id') == $propiedad->id ? 'selected' : '' }}>
                    {{ $propiedad->name }} — ${{ number_format($propiedad->price_per_day, 0, ',', '.') }}/día
                </option>
                @endforeach
            </select>
            @endif
        </div>

        {{-- Cliente --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Cliente</h2>
                <div class="flex items-center gap-2 text-sm">
                    <button type="button" @click="existingClient = true"
                        :class="existingClient ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                        Cliente existente
                    </button>
                    <button type="button" @click="existingClient = false"
                        :class="!existingClient ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                        Nuevo cliente
                    </button>
                </div>
            </div>

            <div x-show="existingClient">
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Buscar cliente registrado</label>
                <select name="user_id" :disabled="!existingClient"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Seleccioná un cliente...</option>
                    @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" {{ old('user_id') == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->full_name }} — {{ $cliente->email }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div x-show="!existingClient" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" placeholder="Juan"
                        :disabled="existingClient"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Apellido</label>
                    <input type="text" name="client_last_name" value="{{ old('client_last_name') }}" placeholder="Pérez"
                        :disabled="existingClient"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" placeholder="juan@email.com"
                        :disabled="existingClient"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Teléfono</label>
                    <input type="text" name="client_phone" value="{{ old('client_phone') }}" placeholder="+54 9 11 1234-5678"
                        :disabled="existingClient"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">DNI <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="text" name="client_dni" value="{{ old('client_dni') }}" placeholder="12.345.678"
                        :disabled="existingClient"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        {{-- Fechas y huéspedes --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide">Fechas y huéspedes</h2>
                <button type="button" onclick="abrirCalendarioDisponibilidad()"
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
                    <input type="date" name="check_in" value="{{ old('check_in') }}" required
                        x-ref="checkIn" @change="calcTotal()"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Check-out</label>
                    <input type="date" name="check_out" value="{{ old('check_out') }}" required
                        x-ref="checkOut" @change="calcTotal()"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Hora entrada</label>
                    <input type="time" name="check_in_time" value="{{ old('check_in_time', '14:00') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Hora salida</label>
                    <input type="time" name="check_out_time" value="{{ old('check_out_time', '11:00') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Huéspedes</label>
                    <input type="number" name="guests" value="{{ old('guests', 1) }}" min="1" required
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Total (ARS)</label>
                    <input type="number" name="total_amount" value="{{ old('total_amount') }}" min="0" step="0.01" required
                        placeholder="0" x-ref="total"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        {{-- Notas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Notas internas</h2>
            <textarea name="notes" rows="3" placeholder="Notas visibles solo para vos..."
                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('notes') }}</textarea>
        </div>

        {{-- Estado --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Estado</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Reserva</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="confirmed" {{ old('status', 'confirmed') === 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                        <option value="pending"   {{ old('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Pago</label>
                    <select name="payment_status" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="unpaid"  {{ old('payment_status', 'unpaid') === 'unpaid' ? 'selected' : '' }}>Pendiente</option>
                        <option value="paid"     {{ old('payment_status') === 'paid' ? 'selected' : '' }}>Pagado</option>
                        <option value="refunded" {{ old('payment_status') === 'refunded' ? 'selected' : '' }}>Reembolsado</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm">
            Crear reserva
        </button>

    </form>
</div>

<x-calendar-modal />

@push('scripts')
<script src="{{ asset('js/reservas-calendar.js') }}"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reservaForm', () => ({
        existingClient: true,
        selectedProperty: null,
        properties: @json($propiedades->map(fn($p) => ['id' => $p->id, 'price_per_day' => (float) $p->price_per_day])->values()),

        calcTotal() {},
    }));
});

/* ── Calendario disponibilidad ── */
const _dispReservas = @json($reservasPorPropiedad);

function abrirCalendarioDisponibilidad() {
    const propId   = document.querySelector('[name="property_id"]')?.value;
    const propName = document.querySelector('[name="property_id"] option:checked')?.text ?? 'Propiedad';
    if (!propId) { alert('Primero seleccioná una propiedad.'); return; }
    calendarModal.open(propName, _dispReservas[propId] ?? []);
}
</script>
@endpush

@endsection
