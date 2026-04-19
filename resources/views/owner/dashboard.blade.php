@extends('layouts.main')
@section('title', 'Panel Propietario')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

@php $hasSub = auth()->user()->hasSubscription() || auth()->user()->isAdmin(); @endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-0">
    <h1 class="text-2xl font-black text-gray-900">Hola, {{ auth()->user()->name }}</h1>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    @include('components.subscription-alert')

    @if($propiedades->isEmpty())
        {{-- Sin propiedades (con o sin suscripción) --}}
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-indigo-200 mt-4">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>

            <p class="text-gray-400 text-sm mb-6">Publicá tu primera propiedad y empezá a recibir reservas</p>
            <a href="{{ route('owner.properties.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Publicar propiedad
            </a>
        </div>

    @else
        {{-- Panel completo --}}

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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

        {{-- 2 columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Propiedades --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Mis Propiedades</h2>
                    <a href="{{ route('owner.properties.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-700">Ver todas</a>
                </div>
                <div class="space-y-3">
                    @foreach($propiedades->take(5) as $propiedad)
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
                    @endforeach
                </div>
            </div>

            {{-- Reservas recientes --}}
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
                        <p class="text-gray-400 text-sm">No hay reservas todavía</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    @endif

</div>

@endsection
@if(session("subscription_activated"))
@push("scripts")
<div class="modal fade" id="modalSuscripcionActiva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0" style="border-radius:1.75rem;overflow:hidden;box-shadow:0 32px 64px -12px rgba(0,0,0,.3);">

            {{-- Header --}}
            <div style="position:relative;padding:1.75rem 1.5rem 1.5rem;background:linear-gradient(135deg,#4338ca 0%,#6d28d9 55%,#9333ea 100%);overflow:hidden;">
                <div style="position:absolute;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.08);top:-28px;right:-28px;pointer-events:none;"></div>
                <div style="position:absolute;width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.1);top:16px;right:52px;pointer-events:none;"></div>
                <button type="button" data-bs-dismiss="modal"
                        style="position:absolute;top:14px;right:14px;width:30px;height:30px;background:rgba(255,255,255,.2);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                    <svg width="13" height="13" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:54px;height:54px;border-radius:1rem;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.65rem;flex-shrink:0;">🎉</div>
                    <div>
                        <div style="font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:.3rem;">Pago aprobado</div>
                        <div style="font-size:1.15rem;font-weight:900;color:#fff;line-height:1.2;">¡Suscripción activada!</div>
                    </div>
                </div>
            </div>

            {{-- Cuerpo --}}
            <div style="padding:1.4rem 1.5rem;">
                <p style="font-size:.875rem;color:#6b7280;line-height:1.6;margin-bottom:1.1rem;">A partir de ahora tenés acceso completo a todos los beneficios:</p>

                <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.2rem;">
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;border-radius:.875rem;background:#f0fdf4;">
                        <span style="font-size:1.1rem;">📞</span>
                        <div><div style="font-size:.82rem;font-weight:700;color:#111827;">Contacto directo</div><div style="font-size:.75rem;color:#9ca3af;">Ves los datos de tus clientes en cada reserva</div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;border-radius:.875rem;background:#eff6ff;">
                        <span style="font-size:1.1rem;">💬</span>
                        <div><div style="font-size:.82rem;font-weight:700;color:#111827;">Mensajes desbloqueados</div><div style="font-size:.75rem;color:#9ca3af;">Leé y respondé mensajes sin restricciones</div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;border-radius:.875rem;background:#fdf4ff;">
                        <span style="font-size:1.1rem;">📈</span>
                        <div><div style="font-size:.82rem;font-weight:700;color:#111827;">Panel completo</div><div style="font-size:.75rem;color:#9ca3af;">Gestioná propiedades, reservas y ganancias</div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;border-radius:.875rem;background:#fff7ed;">
                        <span style="font-size:1.1rem;">💰</span>
                        <div><div style="font-size:.82rem;font-weight:700;color:#111827;">Más ventas</div><div style="font-size:.75rem;color:#9ca3af;">Posicionamiento, marketing y asesoría de ventas</div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;border-radius:.875rem;background:#f0f9ff;">
                        <span style="font-size:1.1rem;">📱</span>
                        <div><div style="font-size:.82rem;font-weight:700;color:#111827;">Gestión de redes y publicaciones</div><div style="font-size:.75rem;color:#9ca3af;">Gestioná todas tus redes sociales y publicaciones desde el panel</div></div>
                    </div>
                </div>

                <div style="padding:.9rem 1rem;border-radius:1rem;background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1px dashed #c4b5fd;text-align:center;margin-bottom:1.2rem;">
                    <div style="font-size:.78rem;font-weight:700;color:#4f46e5;margin-bottom:.6rem;">🚀 Próximamente te contactamos para entregarte:</div>
                    <div style="display:flex;justify-content:center;gap:1.5rem;">
                        <div><div style="font-size:1.4rem;">🤖</div><div style="font-size:.73rem;font-weight:600;color:#111827;">Chatbot inteligente WhatsApp</div></div>
                        <div><div style="font-size:1.4rem;">🌐</div><div style="font-size:.73rem;font-weight:600;color:#111827;">Sitio web</div></div>
                    </div>
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:.6rem;">Te escribiremos al email registrado para coordinar los detalles.</div>
                </div>

                <button type="button" data-bs-dismiss="modal"
                    style="width:100%;padding:.75rem;border:none;border-radius:.875rem;background:linear-gradient(135deg,#4338ca,#6d28d9);color:#fff;font-weight:700;font-size:.875rem;cursor:pointer;">
                    ¡Empezar a usar mis beneficios!
                </button>
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var modal = new bootstrap.Modal(document.getElementById("modalSuscripcionActiva"), { backdrop: "static" });
    modal.show();
});
</script>
@endpush
@endif
