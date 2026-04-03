@extends('layouts.main')
@section('title', 'Panel Propietario')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-0">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Hola, {{ auth()->user()->name }}</h1>
        </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    @include('components.subscription-alert')

    @if(auth()->user()->hasSubscription() || auth()->user()->isAdmin())
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Propiedades</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['total_propiedades'] }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $stats['active_propiedades'] }} activas</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Reservas</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['total_reservations'] }}</p>
            <p class="text-xs text-yellow-600 mt-1">{{ $stats['pending_reservations'] }} pendientes</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Confirmadas</p>
            <p class="text-3xl font-black text-green-600">{{ $stats['confirmed_reservations'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Ganancias</p>
            <p class="text-2xl font-black text-indigo-600">${{ number_format($stats['total_earnings'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">ARS cobrados</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Propiedades --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Mis Propiedades</h2>
                <a href="{{ route('owner.properties.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-700">Ver todas</a>
            </div>
            <div class="space-y-3">
                @forelse($propiedades->take(5) as $propiedad)
                <a href="{{ route('owner.properties.edit', $propiedad) }}"
                   class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-4 hover:border-indigo-200 hover:shadow-md transition-all group">
                    <img src="{{ $propiedad->cover_image_url }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0"
                         onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=200&q=80'">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ $propiedad->name }}</h3>
                        <p class="text-sm text-gray-400">{{ $propiedad->city }} • ${{ number_format($propiedad->price_per_day, 0, ',', '.') }}/dia</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0
                        {{ $propiedad->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $propiedad->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $propiedad->status === 'inactive' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($propiedad->status) }}
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @empty
                <div class="text-center py-8 bg-white rounded-2xl border border-dashed border-gray-200">
                    <a href="{{ route('owner.properties.create') }}" class="text-indigo-600 font-semibold text-sm hover:text-indigo-700">Publicar mi primera propiedad</a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent reservations --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Reservas Recientes</h2>
                <a href="{{ route('owner.reservations') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-700">Ver todas</a>
            </div>
            <div class="space-y-3">
                @forelse($recentReservations->take(5) as $reservation)
                <a href="{{ route('owner.reservations.show', $reservation) }}"
                   class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 block hover:border-indigo-200 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-gray-900 text-sm group-hover:text-indigo-600 transition-colors">{{ $reservation->user->full_name }}</p>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $reservation->status_label }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-1">{{ $reservation->property?->name ?? '—' }}</p>
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-400">{{ $reservation->check_in->format('d/m/Y') }} - {{ $reservation->check_out->format('d/m/Y') }}</p>
                        <p class="font-bold text-indigo-600 text-sm">${{ number_format($reservation->total_amount, 0, ',', '.') }}</p>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 bg-white rounded-2xl border border-dashed border-gray-200">
                    <p class="text-gray-400 text-sm">No hay reservas todavia</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif {{-- hasSubscription --}}
</div>

@endsection
