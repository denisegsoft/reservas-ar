@extends('layouts.main')
@section('title', 'Panel de Administracion')
@section('content')

<div class="bg-gradient-to-r from-orange-600 to-red-600 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">Panel de Administración</h1>
                <p class="text-orange-200 text-sm mt-1">Control total del sistema</p>
            </div>
            <a href="{{ route('admin.settings') }}" class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configuración
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Usuarios</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Propiedades</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['total_propiedades'] }}</p>
            <p class="text-xs text-orange-600 mt-1">{{ $stats['pending_propiedades'] }} pendientes aprobacion</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Reservas</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['total_reservations'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Ingresos Totales</p>
            <p class="text-2xl font-black text-green-600">${{ number_format($stats['total_payments'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">ARS procesados</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Reseñas Pendientes</p>
            <p class="text-3xl font-black text-yellow-600">{{ $stats['pending_reviews'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="space-y-2 mt-1">
                <a href="{{ route('admin.properties') }}" class="block text-sm text-indigo-600 font-medium hover:text-indigo-700">Gestionar Propiedades</a>
                <a href="{{ route('admin.users') }}" class="block text-sm text-indigo-600 font-medium hover:text-indigo-700">Gestionar Usuarios</a>
                <a href="{{ route('admin.reviews') }}" class="block text-sm text-indigo-600 font-medium hover:text-indigo-700">Gestionar Reseñas</a>
            </div>
        </div>
    </div>

    @if($pendingPropiedades->count())
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Propiedades Pendientes de Aprobacion</h2>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-50">
                @foreach($pendingPropiedades as $propiedad)
                <div class="flex items-center gap-4 p-4">
                    <img src="{{ $propiedad->cover_image_url }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0"
                         onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=200&q=80'">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ $propiedad->name }}</p>
                        <p class="text-sm text-gray-500">{{ $propiedad->city }} • Propietario: {{ $propiedad->owner->full_name }}</p>
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('admin.properties.approve', $propiedad) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                                Aprobar
                            </button>
                        </form>
                        <form action="{{ route('admin.properties.reject', $propiedad) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                                Rechazar
                            </button>
                        </form>
                        <a href="{{ route('properties.show', $propiedad->slug) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                            Ver
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div>
        <h2 class="text-lg font-bold text-gray-900 mb-4">Reservas Recientes</h2>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Propiedad</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentReservations as $reservation)
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-500">#{{ $reservation->id }}</td>
                            <td class="py-3 px-4 text-sm text-gray-700">{{ $reservation->user?->full_name ?? '—' }}</td>
                            <td class="py-3 px-4 text-sm text-gray-700">{{ $reservation->property?->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-sm font-bold text-gray-900">${{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ $reservation->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
