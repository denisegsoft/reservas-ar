@php
    $user = auth()->user();
@endphp

@if($user && !$user->isAdmin() && !$user->hasSubscription() && $user->propiedades()->exists())
@php
    $price     = \App\Http\Controllers\SubscriptionController::price();
    $basePrice = \App\Http\Controllers\SubscriptionController::basePrice();
    $discount  = \App\Http\Controllers\SubscriptionController::discountInfo();
    $views     = max((int) $user->propiedades()->sum('views_count'), 10);
    $messages  = \App\Models\Message::where('receiver_id', $user->id)->count();
@endphp
<div class="mb-8 bg-indigo-50 border border-indigo-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-violet-500"></div>
    <div class="p-5 sm:p-6">

        {{-- Fila superior --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
            <div class="w-11 h-11 rounded-xl bg-white border border-indigo-200 flex items-center justify-center text-xl flex-shrink-0 shadow-sm">🔒</div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-bold text-gray-900 text-base">Tu cuenta está limitada</p>
                </div>
                <p class="text-gray-500 text-sm mt-0.5">
                    Activá tu cuenta y desbloqueá todas las herramientas para gestionar y hacer crecer tu negocio.
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    @if($discount)
                        <span class="text-sm text-gray-400 font-medium" style="text-decoration: line-through;">${{ number_format($basePrice, 0, ',', '.') }} ARS</span>
                        <span class="text-lg font-black text-indigo-600">${{ number_format($price, 0, ',', '.') }} ARS</span>
                        <span class="text-xs font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">🏷️ {{ $discount['pct'] }}% OFF{{ $discount['label'] ? ' · ' . $discount['label'] : '' }}</span>
                    @else
                        <span class="text-lg font-black text-indigo-600">${{ number_format($price, 0, ',', '.') }} ARS</span>
                    @endif
                    <span class="text-xs text-gray-400">· pago único, sin renovaciones</span>
                </div>
            </div>

            <div class="flex-shrink-0 flex flex-col items-end gap-1.5">
                <a href="{{ route('subscription.pay') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold px-5 py-2.5 rounded-xl transition-colors text-sm shadow-sm whitespace-nowrap">
                    Activar ahora
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <div class="flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <span class="text-xs text-gray-400">Pago seguro vía</span>
                    <img src="https://http2.mlstatic.com/frontend-assets/mp-web-navigation/ui-navigation/5.19.1/mercadopago/logo__large@2x.png"
                         alt="MercadoPago" class="h-3.5 object-contain opacity-70">
                </div>
            </div>
        </div>

        {{-- Beneficios incluidos --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
            <div class="flex items-center gap-2 bg-white/70 border border-indigo-100 rounded-xl px-3 py-2">
                <span class="text-base flex-shrink-0">🤖</span>
                <span class="text-xs text-gray-600 font-medium leading-tight">Chatbot inteligente para tu WhatsApp 24/7</span>
            </div>
            <div class="flex items-center gap-2 bg-white/70 border border-indigo-100 rounded-xl px-3 py-2">
                <span class="text-base flex-shrink-0">🌐</span>
                <span class="text-xs text-gray-600 font-medium leading-tight">Sitio web profesional</span>
            </div>
            <div class="flex items-center gap-2 bg-white/70 border border-indigo-100 rounded-xl px-3 py-2">
                <span class="text-base flex-shrink-0">📊</span>
                <span class="text-xs text-gray-600 font-medium leading-tight">Panel personalizado para gestionar reservas, clientes, ventas completo</span>
            </div>
        </div>

        {{-- Mensaje de impacto --}}
        @if($views > 0)
        {{-- <div class="flex items-center gap-1.5 text-sm text-gray-600 bg-red-50 border border-red-100 rounded-xl px-3 py-2">
            <span class="text-base">🔥</span>
            <span>Tus propiedades ya tienen visitas —</span>
            <strong class="text-red-600">hay clientes que quieren contactarte y no pueden.</strong>
        </div> --}}
        @endif

    </div>
</div>
@endif
