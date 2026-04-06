@extends('layouts.main')
@section('title', 'Mis Reservas')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Mis Reservas</h1>
            <p class="text-gray-500 text-sm">Tus reservas realizadas</p>
        </div>
        <a href="{{ route('properties.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
            Nueva Reserva
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if($reservations->count())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Propiedad</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fechas</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Personas</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pago</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($reservations as $reservation)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 px-4">
                            <p class="text-sm font-semibold text-gray-800">{{ $reservation->property->name }}</p>
                            <p class="text-xs text-gray-400">{{ $reservation->property->city }}, {{ $reservation->property->state }}</p>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ $reservation->check_in->format('d/m/Y') }}<br>
                            <span class="text-gray-400">{{ $reservation->check_out->format('d/m/Y') }}</span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ $reservation->guests }}
                        </td>
                        <td class="py-3 px-4 text-sm font-bold text-gray-900">
                            ${{ number_format($reservation->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                                {{ $reservation->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $reservation->payment_status === 'paid'     ? 'bg-green-100 text-green-700' : '' }}
                                {{ $reservation->payment_status === 'unpaid'   ? 'bg-orange-100 text-orange-600' : '' }}
                                {{ $reservation->payment_status === 'refunded' ? 'bg-gray-100 text-gray-600' : '' }}">
                                {{ $reservation->payment_status === 'paid' ? 'Pagado' : ($reservation->payment_status === 'refunded' ? 'Reembolsado' : 'Pendiente') }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                @if($reservation->isPending() && !$reservation->isPaid())
                                <a href="{{ route('reservations.payment', $reservation) }}"
                                   class="text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-2.5 py-1 rounded-lg transition-colors">
                                    Pagar
                                </a>
                                @endif
                                <a href="{{ route('reservations.show', $reservation) }}"
                                   class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                    Ver detalle
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $reservations->links() }}</div>

    @else
    <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
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
