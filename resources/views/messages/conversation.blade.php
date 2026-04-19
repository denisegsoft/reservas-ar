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

</div>

@push('styles')
@vite(['resources/css/pages/messages-conversation.css'])
@endpush

@push('scripts')
<script>
window.CHAT_HAS_RESERVATION = {{ $reservation ? 'true' : 'false' }};
window.CHAT_RESERVATION_ID  = {{ $reservation?->id ?? 'null' }};
window.CHAT_STORE_URL        = '{{ route('messages.store', $user) }}';
window.CHAT_CSRF_TOKEN       = '{{ csrf_token() }}';
window.CHAT_MESSAGES         = @json($messagesData);
</script>
@vite(['resources/js/pages/messages-conversation.js'])
@endpush

@endsection
