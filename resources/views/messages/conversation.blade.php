@extends('layouts.main')
@section('title', 'Conversacion con ' . $user->full_name)
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('messages.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-10 h-10 rounded-full object-cover">
        <div>
            <h1 class="font-bold text-gray-900">{{ $user->full_name }}</h1>
            <p class="text-xs text-gray-400 capitalize">{{ $user->role }}</p>
        </div>
    </div>

    {{-- Reserva relacionada --}}
    @if($reservation)
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-indigo-500 font-medium">Reserva #{{ $reservation->id }}</p>
            <p class="font-semibold text-indigo-900 truncate">{{ $reservation->property->name }}</p>
            <p class="text-xs text-indigo-600">{{ $reservation->check_in->format('d/m/Y') }} — {{ $reservation->check_out->format('d/m/Y') }} · ${{ number_format($reservation->total_amount, 0, ',', '.') }} ARS</p>
        </div>
        <a href="{{ route('reservations.show', $reservation) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex-shrink-0">Ver</a>
    </div>
    @endif

    {{-- Mensajes --}}
    @if($ownerBlocked)
    {{-- Vista bloqueada para propietario sin suscripción --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        {{-- Mensajes difuminados como preview --}}
        <div class="p-6 min-h-[200px] relative select-none pointer-events-none" aria-hidden="true">
            <div class="space-y-4 blur-sm opacity-40">
                <div class="flex gap-2 justify-start">
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex-shrink-0 mt-1"></div>
                    <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-2.5 max-w-xs">
                        <div class="h-3 bg-gray-300 rounded w-32 mb-1.5"></div>
                        <div class="h-3 bg-gray-300 rounded w-20"></div>
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <div class="bg-indigo-100 rounded-2xl rounded-br-sm px-4 py-2.5 max-w-xs">
                        <div class="h-3 bg-indigo-200 rounded w-40 mb-1.5"></div>
                        <div class="h-3 bg-indigo-200 rounded w-24"></div>
                    </div>
                </div>
                <div class="flex gap-2 justify-start">
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex-shrink-0 mt-1"></div>
                    <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-2.5 max-w-xs">
                        <div class="h-3 bg-gray-300 rounded w-48 mb-1.5"></div>
                        <div class="h-3 bg-gray-300 rounded w-28"></div>
                    </div>
                </div>
            </div>
            {{-- Overlay con CTA --}}
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="bg-white/95 backdrop-blur-sm rounded-3xl border border-indigo-100 shadow-xl p-8 text-center max-w-sm mx-4">
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">🔒</div>
                    <h3 class="font-black text-gray-900 text-lg mb-2">Mensajes bloqueados</h3>
                    <p class="text-gray-500 text-sm mb-5 leading-relaxed">
                        Activá tu suscripción por <strong class="text-indigo-600">${{ number_format(\App\Models\Setting::get('subscription_price', '3000'), 0, ',', '.') }} ARS</strong> (pago único)
                        para leer y responder los mensajes de tus clientes.
                    </p>
                    <a href="{{ route('subscription.payment') }}"
                       class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-2xl transition-colors text-sm shadow-lg shadow-indigo-100">
                        Activar suscripción
                    </a>
                    <p class="text-xs text-gray-400 mt-3">Pago único · Sin renovaciones</p>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Vista normal con mensajes --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-4" x-data="chat">
        <div class="p-6 space-y-4 min-h-[300px] max-h-[500px] overflow-y-auto" x-ref="container">
            <template x-if="messages.length === 0">
                <div class="text-center py-12">
                    <p class="text-gray-400 text-sm">No hay mensajes aun. Envia el primero!</p>
                </div>
            </template>
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.mine ? 'justify-end' : 'justify-start'" class="flex gap-2">
                    <template x-if="!msg.mine">
                        <img src="{{ $user->avatar_url }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0 mt-1">
                    </template>
                    <div class="max-w-xs lg:max-w-md">
                        <template x-if="msg.reservation_id && !hasReservation">
                            <p class="text-xs text-gray-400 mb-1" :class="msg.mine ? 'text-right' : ''">
                                Re: Reserva #<span x-text="msg.reservation_id"></span>
                            </p>
                        </template>
                        <div :class="msg.mine ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-900 rounded-bl-sm'"
                            class="px-4 py-2.5 rounded-2xl">
                            <p class="text-sm leading-relaxed" x-text="msg.body"></p>
                        </div>
                        <p class="text-xs text-gray-400 mt-1" :class="msg.mine ? 'text-right' : ''">
                            <span x-text="msg.created_at"></span>
                            <template x-if="msg.mine && msg.read_at">
                                <span> · <span class="text-indigo-400">Leido</span></span>
                            </template>
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-100 p-4">
            <div class="flex gap-3">
                <textarea x-model="body" rows="1" maxlength="1000"
                    placeholder="Escribi tu mensaje..."
                    :disabled="sending"
                    @keydown.enter.prevent="if(!$event.shiftKey) send()"
                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none transition-all disabled:opacity-50"></textarea>
                <button @click="send()" :disabled="sending || !body.trim()"
                        class="w-10 h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl flex items-center justify-center transition-all flex-shrink-0 self-end disabled:opacity-50">
                    <svg x-show="!sending" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span x-show="sending" class="flex gap-0.5 items-center">
                        <span style="width:6px;height:6px;background:white;border-radius:50%;animation:bounce 0.8s infinite;animation-delay:0ms"></span>
                        <span style="width:6px;height:6px;background:white;border-radius:50%;animation:bounce 0.8s infinite;animation-delay:160ms"></span>
                        <span style="width:6px;height:6px;background:white;border-radius:50%;animation:bounce 0.8s infinite;animation-delay:320ms"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chat', () => ({
            body: '',
            sending: false,
            hasReservation: {{ $reservation ? 'true' : 'false' }},
            reservationId: {{ $reservation?->id ?? 'null' }},
            storeUrl: '{{ route('messages.store', $user) }}',
            csrfToken: '{{ csrf_token() }}',
            messages: @json($messagesData),

            init() {
                this.$nextTick(() => {
                    this.$refs.container.scrollTop = this.$refs.container.scrollHeight;
                });
            },

            async send() {
                if (!this.body.trim() || this.sending) return;
                this.sending = true;
                const text = this.body;
                this.body = '';
                try {
                    const res = await fetch(this.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ body: text, reservation_id: this.reservationId }),
                    });
                    if (!res.ok) { this.body = text; return; }
                    const msg = await res.json();
                    this.messages.push({ ...msg, mine: true, read_at: null });
                    this.$nextTick(() => {
                        this.$refs.container.scrollTop = this.$refs.container.scrollHeight;
                    });
                } finally {
                    this.sending = false;
                }
            },
        }));
    });
</script>
@endpush

@endsection
