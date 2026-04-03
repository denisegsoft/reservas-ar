@extends('layouts.main')
@section('title', 'Reservas recibidas')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Reservas recibidas</h1>
            <p class="text-gray-500 text-sm">Gestioná las reservas de tus propiedades</p>
        </div>
        <a href="{{ route('owner.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Panel principal</a>
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
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Propiedad</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fechas</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pago</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($reservations as $reservation)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 px-4 text-sm text-gray-500">#{{ $reservation->id }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <img src="{{ $reservation->user->avatar_url }}" class="w-7 h-7 rounded-full" alt="">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $reservation->user->full_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $reservation->guests }} personas</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $reservation->property->name }}</td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ $reservation->check_in->format('d/m/Y') }}<br>
                            <span class="text-gray-400">{{ $reservation->check_out->format('d/m/Y') }}</span>
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
                                {{ $reservation->payment_status === 'unpaid'  ? 'bg-orange-100 text-orange-600' : '' }}
                                {{ $reservation->payment_status === 'refunded' ? 'bg-gray-100 text-gray-600' : '' }}">
                                {{ $reservation->payment_status === 'paid' ? 'Pagado' : ($reservation->payment_status === 'refunded' ? 'Reembolsado' : 'Pendiente') }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('owner.reservations.show', $reservation) }}"
                               class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                Gestionar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $reservations->links() }}</div>
    @else
    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
        <p class="text-gray-400">No hay reservas todavia.</p>
    </div>
    @endif


</div>

@endsection
