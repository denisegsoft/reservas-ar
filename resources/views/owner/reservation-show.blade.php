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
        <div>
            <h1 class="text-2xl font-black text-gray-900">Reserva #{{ $reservation->id }}</h1>
            <p class="text-gray-500 text-sm">{{ $reservation->property->name }}</p>
        </div>
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

    <form action="{{ route('owner.reservations.update', $reservation) }}" method="POST" class="space-y-6">
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
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Fechas y huéspedes</h2>
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
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Total (ARS)</label>
                    <input type="number" name="total_amount" value="{{ $reservation->total_amount }}" min="0" step="0.01"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
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
                        <option value="completed" @selected($reservation->status === 'completed')>Completada</option>
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
            </div>
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm">
            Guardar cambios
        </button>

    </form>
</div>


@endsection
