@extends('layouts.main')
@section('title', 'Mis Reservas')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-gray-900">Mis Reservas</h1>
        <a href="{{ route('properties.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
            Nueva Reserva
        </a>
    </div>

    @if($reservations->count())
    <div class="space-y-4">
        @foreach($reservations as $reservation)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row">
                <div class="sm:w-40 sm:flex-shrink-0">
                    <img src="{{ $reservation->property->cover_image_url }}" alt="{{ $reservation->property->name }}"
                         class="w-full h-40 sm:h-full object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&q=80'">
                </div>
                <div class="flex-1 p-5">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $reservation->property->name }}</h3>
                            <p class="text-gray-500 text-sm">{{ $reservation->property->city }}, {{ $reservation->property->state }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0
                            {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ $reservation->status_label }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                        <span>{{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }}</span>
                        <span class="hidden sm:inline">•</span>
                        <span class="hidden sm:inline">{{ $reservation->total_days }} noches</span>
                        <span class="hidden sm:inline">•</span>
                        <span class="hidden sm:inline">{{ $reservation->guests }} personas</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-900">${{ number_format($reservation->total_amount, 0, ',', '.') }} ARS</span>
                            @if($reservation->payment_status === 'paid')
                            <span class="ml-2 text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Pagado</span>
                            @else
                            <span class="ml-2 text-xs font-medium text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Pendiente pago</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($reservation->isPending() && !$reservation->isPaid())
                            <a href="{{ route('reservations.payment', $reservation) }}"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                Pagar
                            </a>
                            @endif
                            <a href="{{ route('reservations.show', $reservation) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                Ver detalle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $reservations->links() }}</div>

    @else
    <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
        <div class="text-6xl mb-4">&#128197;</div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Todavia no tenes reservas</h3>
        <p class="text-gray-500 mb-6">Explora nuestras propiedades y reserva la tuya hoy.</p>
        <a href="{{ route('properties.index') }}" class="bg-indigo-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-colors">
            Explorar Propiedades
        </a>
    </div>
    @endif
</div>

@endsection
