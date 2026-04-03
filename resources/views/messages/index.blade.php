@extends('layouts.main')
@section('title', 'Mensajes')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Mensajes</h1>
            <p class="text-sm text-gray-500 mt-1">Tus conversaciones con propietarios y clientes</p>
        </div>
    </div>

    {{-- Banner bloqueo para owners sin suscripción --}}
    @if(auth()->user()->isOwner() && !auth()->user()->isAdmin() && !auth()->user()->hasSubscription())
    <div class="mb-6 relative overflow-hidden rounded-3xl shadow-md"
         style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
        <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg flex-shrink-0">🔒</div>
                <div>
                    <p class="text-white font-bold text-sm">Mensajes bloqueados</p>
                    <p class="text-indigo-200 text-xs mt-0.5 max-w-xs">
                        Podés recibir mensajes, pero no leerlos ni responder hasta activar tu suscripción.
                    </p>
                </div>
            </div>
            <a href="{{ route('subscription.payment') }}"
               class="flex-shrink-0 bg-white text-indigo-700 font-bold px-4 py-2 rounded-xl text-xs hover:bg-indigo-50 transition-colors shadow whitespace-nowrap">
                Activar por ${{ number_format(\App\Models\Setting::get('subscription_price', '3000'), 0, ',', '.') }} →
            </a>
        </div>
    </div>
    @endif

    @if($conversations->isEmpty())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">&#128172;</div>
            <h3 class="font-bold text-gray-900 mb-2">Sin mensajes aun</h3>
            <p class="text-gray-500 text-sm mb-6">Cuando hagas o recibas una reserva, podes contactarte desde aqui.</p>
            <a href="{{ route('properties.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-all text-sm">
                Explorar espacios
            </a>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-50">
            @foreach($conversations as $msg)
                @php
                    $otherId = $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
                    $other = $users[$otherId] ?? null;
                    $isUnread = $msg->receiver_id === $userId && !$msg->read_at;
                @endphp
                @if($other)
                <a href="{{ route('messages.conversation', $other->id) }}"
                   class="flex items-center gap-4 px-6 py-4 hover:bg-indigo-50/50 transition-colors">
                    <div class="relative flex-shrink-0">
                        <img src="{{ $other->avatar_url }}" alt="{{ $other->full_name }}"
                             class="w-12 h-12 rounded-full object-cover">
                        @if($isUnread)
                            <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-indigo-500 rounded-full border-2 border-white"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-2">
                            <p class="font-{{ $isUnread ? 'bold' : 'semibold' }} text-gray-900 truncate">{{ $other->full_name }}</p>
                            <span class="text-xs text-gray-400 flex-shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm {{ $isUnread ? 'text-gray-900 font-medium' : 'text-gray-500' }} truncate mt-0.5">
                            @if($msg->sender_id === $userId)<span class="text-gray-400">Vos: </span>@endif
                            {{ $msg->body }}
                        </p>
                    </div>
                </a>
                @endif
            @endforeach
        </div>
    @endif
</div>

@endsection
