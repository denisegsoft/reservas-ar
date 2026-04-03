@extends('layouts.main')
@section('title', 'Admin — Suscripciones')
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Pagos de Suscripción</h1>
        <p class="text-gray-500 text-sm mt-1">Historial completo de pagos de propietarios</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total intentos</p>
            <p class="text-2xl font-black text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Aprobados</p>
            <p class="text-2xl font-black text-green-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pendientes</p>
            <p class="text-2xl font-black text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Recaudado</p>
            <p class="text-2xl font-black text-indigo-600">${{ number_format($stats['revenue'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide">#ID</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide">Propietario</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide">Estado</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide hidden sm:table-cell">Monto</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide hidden md:table-cell">MP Preference</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide hidden md:table-cell">MP Payment</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide hidden lg:table-cell">Método</th>
                    <th class="text-left font-semibold text-gray-500 px-5 py-3.5 text-xs uppercase tracking-wide">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-4 text-gray-400 text-xs font-mono">{{ $payment->id }}</td>

                    <td class="px-5 py-4">
                        @if($payment->user)
                        <div>
                            <p class="font-semibold text-gray-900">{{ $payment->user->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $payment->user->email }}</p>
                        </div>
                        @else
                        <span class="text-gray-400 text-xs">Usuario eliminado</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        @php
                            $colors = [
                                'initiated' => 'bg-gray-100 text-gray-600',
                                'pending'   => 'bg-yellow-100 text-yellow-700',
                                'approved'  => 'bg-green-100 text-green-700',
                                'rejected'  => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-red-100 text-red-600',
                            ];
                            $labels = [
                                'initiated' => 'Iniciado',
                                'pending'   => 'Pendiente',
                                'approved'  => 'Aprobado',
                                'rejected'  => 'Rechazado',
                                'cancelled' => 'Cancelado',
                            ];
                        @endphp
                        <div>
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $colors[$payment->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $labels[$payment->status] ?? $payment->status }}
                            </span>
                            @if($payment->mp_status_detail)
                            <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $payment->mp_status_detail }}</p>
                            @endif
                        </div>
                    </td>

                    <td class="px-5 py-4 hidden sm:table-cell">
                        <span class="font-semibold text-gray-800">${{ number_format($payment->amount, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400 ml-1">ARS</span>
                    </td>

                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($payment->mp_preference_id)
                        <span class="font-mono text-xs text-gray-500 truncate block max-w-[120px]" title="{{ $payment->mp_preference_id }}">
                            {{ Str::limit($payment->mp_preference_id, 20) }}
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($payment->mp_payment_id)
                        <span class="font-mono text-xs text-gray-500">{{ $payment->mp_payment_id }}</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    <td class="px-5 py-4 hidden lg:table-cell">
                        @if($payment->payment_type)
                        <span class="text-xs text-gray-600">{{ $payment->payment_type }}</span>
                        @if($payment->payment_method)
                        <span class="text-xs text-gray-400 block">{{ $payment->payment_method }}</span>
                        @endif
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        <p class="text-xs text-gray-600">{{ $payment->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->created_at->format('H:i') }}</p>
                        @if($payment->paid_at)
                        <p class="text-xs text-green-600 font-medium mt-0.5">Pagado {{ $payment->paid_at->format('d/m H:i') }}</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">
                        No hay registros de pagos todavía.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="mt-5">{{ $payments->links() }}</div>
    @endif

</div>

@endsection
