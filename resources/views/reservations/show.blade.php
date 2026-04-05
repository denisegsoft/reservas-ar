@extends('layouts.main')
@section('title', 'Reserva #' . $reservation->id)
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @if(session('reservation_created'))
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-semibold text-green-800">¡Reserva enviada correctamente!</p>
            <p class="text-sm text-green-700 mt-0.5">Tu solicitud fue recibida. El propietario la revisará y te confirmará a la brevedad.</p>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Reserva #{{ $reservation->id }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $reservation->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <span class="px-4 py-1.5 rounded-full text-sm font-semibold
            @if($reservation->status === 'confirmed') bg-green-100 text-green-700
            @elseif($reservation->status === 'pending') bg-yellow-100 text-yellow-700
            @elseif($reservation->status === 'cancelled') bg-red-100 text-red-700
            @else bg-blue-100 text-blue-700 @endif">
            {{ $reservation->status_label }}
        </span>
    </div>

    {{-- Propiedad summary --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-5">
        <div class="flex gap-4 p-5">
            <img src="{{ $reservation->property->cover_image_url }}" alt="{{ $reservation->property->name }}"
                 class="w-24 h-24 rounded-2xl object-cover flex-shrink-0"
                 onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&q=80'">
            <div>
                <h2 class="font-bold text-gray-900 text-lg">{{ $reservation->property->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $reservation->property->city }}, {{ $reservation->property->state }}</p>
                <a href="{{ route('properties.show', $reservation->property->slug) }}" class="text-indigo-600 text-sm font-medium hover:text-indigo-700 mt-1 inline-block">
                    Ver propiedad
                </a>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-5">
        <h3 class="font-bold text-gray-900 mb-4">Detalles de la reserva</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            <div><p class="text-xs text-gray-400 mb-0.5">Check-in</p><p class="font-semibold">{{ $reservation->check_in->format('d/m/Y') }}</p></div>
            <div><p class="text-xs text-gray-400 mb-0.5">Check-out</p><p class="font-semibold">{{ $reservation->check_out->format('d/m/Y') }}</p></div>
            <div><p class="text-xs text-gray-400 mb-0.5">Personas</p><p class="font-semibold">{{ $reservation->guests }}</p></div>
            <div><p class="text-xs text-gray-400 mb-0.5">Noches</p><p class="font-semibold">{{ $reservation->total_days }}</p></div>
            <div><p class="text-xs text-gray-400 mb-0.5">Precio/dia</p><p class="font-semibold">${{ number_format($reservation->price_per_day, 0, ',', '.') }}</p></div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Pago</p>
                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $reservation->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $reservation->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}
                </span>
            </div>
        </div>
        <div class="border-t border-gray-100 pt-4 space-y-1.5">
            <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>${{ number_format($reservation->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-sm text-gray-600"><span>Cargo servicio</span><span>${{ number_format($reservation->service_fee, 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-bold text-gray-900 border-t border-gray-100 pt-2"><span>Total</span><span>${{ number_format($reservation->total_amount, 0, ',', '.') }} ARS</span></div>
        </div>
    </div>

    {{-- Contacto del propietario (visible si tiene suscripción activa) --}}
    @php $owner = $reservation->property->owner; @endphp
    @if($owner->hasSubscription())
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 mb-5">
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Contacto del Propietario</h3>
        <div class="flex items-start gap-4">
            <img src="{{ $owner->avatar_url }}" class="w-11 h-11 rounded-full object-cover flex-shrink-0">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900">{{ $owner->full_name }}</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                    @if($owner->phone)
                    <p class="text-sm text-gray-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $owner->phone }}
                    </p>
                    @endif
                    @if($owner->whatsapp_link)
                    <a href="{{ $owner->whatsapp_link }}" target="_blank"
                       class="text-sm text-green-600 hover:text-green-700 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3">
        @if($reservation->isPending())
        <a href="{{ route('messages.conversation', $reservation->property->user_id) }}?reservation={{ $reservation->id }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Contactar Propietario
        </a>
        @endif

        @if(!$reservation->isCancelled())
        <form action="{{ route('reservations.cancel', $reservation) }}" method="POST"
              onsubmit="return confirm('Estas seguro que queres cancelar esta reserva?')">
            @csrf
            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-6 py-2.5 rounded-xl transition-all border border-red-200">
                Cancelar Reserva
            </button>
        </form>
        @endif

        <a href="{{ route('reservations.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-xl transition-all">
            Mis Reservas
        </a>
    </div>

    {{-- Review form --}}
    @if($reservation->status === 'completed' && !$reservation->review)
    <div class="mt-8 bg-amber-50 border border-amber-200 rounded-3xl p-6">
        <h3 class="font-bold text-amber-900 mb-4">Deja tu resena</h3>
        <form action="{{ route('reviews.store', $reservation) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-amber-800 mb-2">Puntuacion</label>
                <div class="flex gap-2" x-data="{ rating: 0 }">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only" @click="rating = {{ $i }}">
                        <svg :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-gray-300'" class="w-8 h-8 transition-colors hover:text-amber-400"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </label>
                    @endfor
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-amber-800 mb-2">Comentario</label>
                <textarea name="comment" rows="3" required minlength="10" placeholder="Contanos tu experiencia..."
                    class="w-full px-4 py-3 border border-amber-200 bg-white rounded-xl text-sm focus:ring-2 focus:ring-amber-400 outline-none resize-none"></textarea>
            </div>
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-all">
                Enviar Resena
            </button>
        </form>
    </div>
    @endif
</div>

@endsection
