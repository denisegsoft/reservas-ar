@extends('layouts.main')
@section('title', 'Mis Propiedades')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @include('components.subscription-alert')

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition.opacity
         class="mb-6 bg-green-50 border border-green-200 text-green-800 text-sm px-5 py-3.5 rounded-2xl flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span class="flex-1">{{ session('success') }}</span>
        <button @click="show = false" class="ml-auto text-green-600 hover:text-green-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-gray-900">Mis Propiedades</h1>
        <a href="{{ route('owner.properties.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
            + Nueva Propiedad
        </a>
    </div>

    @if($propiedades->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($propiedades as $propiedad)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="relative">
                <div class="h-44 bg-gray-900 flex items-center justify-center overflow-hidden">
                    <img src="{{ $propiedad->cover_image_url }}" style="max-width:100%;max-height:100%;width:auto;height:auto;display:block;"
                         onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=80'">
                </div>
                <div class="absolute top-3 right-3">
                    @if($propiedad->status === 'pending')
                        <span class="text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200 px-3 py-2 rounded-xl">Pendiente</span>
                    @elseif($propiedad->status === 'active')
                    <form action="{{ route('owner.properties.toggle', $propiedad) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-semibold px-3 py-2 rounded-xl transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Desactivar
                        </button>
                    </form>
                    @else
                    <form action="{{ route('owner.properties.toggle', $propiedad) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-1.5 bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 text-xs font-semibold px-3 py-2 rounded-xl transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Activar
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-gray-900 mb-1">{{ $propiedad->name }}</h3>
                <p class="text-sm text-gray-500 mb-3">{{ $propiedad->city }}, {{ $propiedad->state }}</p>
                <div class="flex items-center justify-between mb-3">
                    <span class="font-bold text-indigo-600">${{ number_format($propiedad->price_per_day, 0, ',', '.') }}/dia</span>
                    <span class="text-xs text-gray-400">{{ $propiedad->capacity }} personas</span>
                </div>
                {{-- Vistas --}}
                <div class="flex items-center gap-1.5 mb-3">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="text-xs text-gray-500">{{ number_format($propiedad->views_count, 0, ',', '.') }} {{ $propiedad->views_count === 1 ? 'vista' : 'vistas' }}</span>
                    @if(!auth()->user()->hasSubscription())
                    <span class="ml-auto text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                        🔒 Sin contacto
                    </span>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('properties.show', $propiedad->slug) }}" class="flex-1 text-center bg-green-100 hover:bg-green-200 text-gray-700 text-sm font-medium py-2 rounded-xl transition-colors">
                        Ver
                    </a>
                    <a href="{{ route('owner.properties.edit', $propiedad) }}" class="flex-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-medium py-2 rounded-xl transition-colors">
                        Editar
                    </a>
                    <button type="button"
                            onclick="confirmDelete('{{ route('owner.properties.destroy', $propiedad) }}', '{{ addslashes($propiedad->name) }}')"
                            class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-3 py-2 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                @php
                    $reservasData = $propiedad->reservations->map(fn($r) => [
                        'id'        => $r->id,
                        'check_in'  => $r->check_in->format('Y-m-d'),
                        'check_out' => $r->check_out->format('Y-m-d'),
                        'status'    => $r->status,
                        'guest'     => $r->user->name ?? 'Sin nombre',
                        'guests'    => $r->guests,
                        'total'     => number_format($r->total_amount, 0, ',', '.'),
                    ]);
                @endphp
                <button type="button"
                        onclick="{{ auth()->user()->hasSubscription() ? 'calendarModal.open(' . Js::from($propiedad->name) . ', ' . Js::from($reservasData) . ')' : 'new bootstrap.Modal(document.getElementById(\'calendarModal\')).show()' }}"
                        class="w-full mt-2 flex items-center justify-center gap-1.5 text-sm font-medium py-2 rounded-xl transition-colors"
                        style="background:#eff6ff;color:#1d4ed8;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Ver Reservas
                    @if($propiedad->reservations->count())
                        <span style="background:#1d4ed8;color:#fff;font-size:.65rem;font-weight:700;border-radius:20px;padding:1px 7px;">{{ $propiedad->reservations->count() }}</span>
                    @endif
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-8">{{ $propiedades->links() }}</div>
    @else
    <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
        <div class="text-6xl mb-4">&#127968;</div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Todavia no publicaste propiedades</h3>
        <p class="text-gray-500 mb-6">Publica tu primera propiedad y empieza a recibir reservas.</p>
        <a href="{{ route('owner.properties.create') }}" class="bg-indigo-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-colors">
            Publicar Primera Propiedad
        </a>
    </div>
    @endif
</div>

@php $hasSub = auth()->user()->hasSubscription() || auth()->user()->isAdmin(); @endphp
<x-calendar-modal
    title="Reservas"
    :locked="!$hasSub"
    :footer-link="$hasSub ? ['href' => route('owner.reservations'), 'label' => 'Ver todas las reservas →'] : null"
/>

{{-- Modal confirmar eliminación (Bootstrap) --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content" style="border:none;border-radius:1.25rem;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
            <div class="modal-body" style="padding:2rem">

                <div style="display:flex;align-items:center;justify-content:center;width:56px;height:56px;background:#fee2e2;border-radius:1rem;margin:0 auto 1.25rem">
                    <svg style="width:28px;height:28px;color:#dc2626" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                <h5 style="font-size:1.125rem;font-weight:700;color:#111827;text-align:center;margin-bottom:.25rem">¿Eliminar propiedad?</h5>
                <p style="font-size:.875rem;color:#6b7280;text-align:center;margin-bottom:.25rem">Vas a eliminar:</p>
                <p id="deleteModalName" style="font-size:.875rem;font-weight:600;color:#1f2937;text-align:center;margin-bottom:.5rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:0 .5rem"></p>
                <p style="font-size:.75rem;color:#9ca3af;text-align:center;margin-bottom:1.5rem">Esta acción es permanente y no se puede deshacer.</p>

                <div style="display:flex;gap:.75rem">
                    <button type="button" data-bs-dismiss="modal"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="button" onclick="submitDelete()"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">
                        Sí, eliminar
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="{{ asset('js/reservas-calendar.js') }}"></script>
<script>
function confirmDelete(action, name) {
    document.getElementById('deleteModalName').textContent = name;
    document.getElementById('deleteForm').action = action;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
function submitDelete() {
    document.getElementById('deleteForm').submit();
}
</script>
@endpush

@endsection
