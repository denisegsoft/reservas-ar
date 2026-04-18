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

    @elseif(!$hasSub)
        {{-- Tiene propiedades pero sin suscripción: solo el banner ya incluido arriba --}}

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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 px-5 pt-5 pb-3" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <div class="w-100 text-center">
                    <div class="display-4 mb-2">🎉</div>
                    <h4 class="modal-title text-white fw-black fs-3">¡Suscripción activada!</h4>
                    <p class="text-white text-opacity-75 mb-0 mt-1">Tu pago fue procesado correctamente</p>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-5 py-4">
                <p class="text-center text-muted mb-4">A partir de ahora tenés acceso completo a todos los beneficios:</p>
                <div class="row g-3 mb-4">
                    <div class="col-6"><div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background:#f0fdf4"><span class="fs-4">📞</span><div><div class="fw-bold text-dark small">Contacto directo</div><div class="text-muted" style="font-size:.8rem">Ves los datos de tus clientes en cada reserva</div></div></div></div>
                    <div class="col-6"><div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background:#eff6ff"><span class="fs-4">💬</span><div><div class="fw-bold text-dark small">Mensajes desbloqueados</div><div class="text-muted" style="font-size:.8rem">Leé y respondé mensajes sin restricciones</div></div></div></div>
                    <div class="col-6"><div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background:#fdf4ff"><span class="fs-4">📈</span><div><div class="fw-bold text-dark small">Panel completo</div><div class="text-muted" style="font-size:.8rem">Gestioná propiedades, reservas y ganancias</div></div></div></div>
                    <div class="col-6"><div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background:#fff7ed"><span class="fs-4">💰</span><div><div class="fw-bold text-dark small">Más ventas</div><div class="text-muted" style="font-size:.8rem">Posicionamiento, marketing y asesoría de ventas</div></div></div></div>
                </div>
                <div class="rounded-3 p-4 text-center" style="background:linear-gradient(135deg,#f0f9ff,#faf5ff);border:1px dashed #c4b5fd">
                    <p class="fw-bold mb-1" style="color:#4f46e5">🚀 Próximamente te contactamos para entregarte:</p>
                    <div class="d-flex justify-content-center gap-4 mt-2 flex-wrap">
                        <div class="text-center"><div class="fs-3">🤖</div><div class="small fw-semibold text-dark">Chatbot WhatsApp</div><div class="text-muted" style="font-size:.75rem">Atención automática 24/7</div></div>
                        <div class="text-center"><div class="fs-3">🌐</div><div class="small fw-semibold text-dark">Sitio web profesional</div><div class="text-muted" style="font-size:.75rem">A medida de tu negocio</div></div>
                    </div>
                    <p class="text-muted mt-3 mb-0" style="font-size:.82rem">Nos comunicaremos al email registrado para coordinar los detalles y comenzar a trabajar juntos.</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-5 pb-5 pt-0 justify-content-center">
                <button type="button" class="btn btn-primary px-5 py-2 rounded-3 fw-bold" data-bs-dismiss="modal" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border:none;">
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
