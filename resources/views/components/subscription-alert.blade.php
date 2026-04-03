@php
    $user = auth()->user();
@endphp

@if($user && !$user->isAdmin() && !$user->hasSubscription() && $user->propiedades()->exists())
@php
    $price    = \App\Http\Controllers\SubscriptionController::price();
    $views    = max((int) $user->propiedades()->sum('views_count'), 10);
    $messages = \App\Models\Message::where('receiver_id', $user->id)->count();
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
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Tus clientes están solicitando reservas pero no pueden contactarte
                    </span>
                </p>
                <p class="text-gray-500 text-sm mt-0.5">
                    Activá por <strong class="text-indigo-600">${{ number_format($price, 0, ',', '.') }} ARS</strong>,
                    pago único sin renovaciones. Desbloqueá el acceso completo para gestionar tus propiedades y ventas.
                </p>
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

        {{-- Mensaje de impacto --}}
        @if($views > 0 || $messages > 0)
        <div class="mb-4 flex flex-wrap items-center gap-x-1 text-sm text-gray-600">
            @if($views > 0)
            <span>🔥 Tus propiedades ya tienen&nbsp;</span>
            <strong class="text-indigo-700">{{ number_format($views, 0, ',', '.') }} visitas</strong>
            <span>—</span>
            <strong class="text-red-600">{{ number_format($views, 0, ',', '.') }} clientes quieren contactarte</strong>
            <span>&nbsp;y no pueden.</span>
            @endif
        </div>
        @endif

    </div>
</div>
@endif
