@extends('layouts.main')
@section('title', 'Coordinar Pago')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">&#128172;</div>
        <h1 class="text-2xl font-black text-gray-900 mb-2">Coordina tu reserva</h1>
        <p class="text-gray-500">Ponete en contacto con el propietario para coordinar el pago y los detalles.</p>
    </div>

    {{-- Resumen --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
            <p class="text-indigo-200 text-sm mb-1">Reserva #{{ $reservation->id }}</p>
            <h2 class="text-xl font-bold">{{ $reservation->property->name }}</h2>
            <p class="text-indigo-200 text-sm mt-1">{{ $reservation->property->city }}, {{ $reservation->property->state }}</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Check-in</p>
                    <p class="font-semibold text-gray-800">{{ $reservation->check_in->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Check-out</p>
                    <p class="font-semibold text-gray-800">{{ $reservation->check_out->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Personas</p>
                    <p class="font-semibold text-gray-800">{{ $reservation->guests }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Dias</p>
                    <p class="font-semibold text-gray-800">{{ $reservation->total_days }}</p>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>${{ number_format($reservation->price_per_day, 0, ',', '.') }} x {{ $reservation->total_days }} dias</span>
                    <span>${{ number_format($reservation->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-black text-lg text-gray-900 border-t border-gray-100 pt-3">
                    <span>Total acordado</span>
                    <span class="text-indigo-600">${{ number_format($reservation->total_amount, 0, ',', '.') }} ARS</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Contactar propietario --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-1">Contacta al propietario</h3>
        <p class="text-sm text-gray-500 mb-5">Coordina la forma de pago y los detalles directamente con el propietario.</p>

        <a href="{{ route('messages.conversation', $reservation->property->user_id) }}?reservation={{ $reservation->id }}"
           class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-200 mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Enviar mensaje al propietario
        </a>

        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl p-4">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm text-amber-800">El pago se coordina directamente con el propietario fuera de la plataforma. Acordar antes de confirmar.</p>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('reservations.show', $reservation) }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
            Volver a la reserva
        </a>
    </div>
</div>

@endsection
