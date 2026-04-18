@extends('layouts.main')
@section('title', 'Admin - Configuración')
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Configuración del sitio</h1>
        <p class="text-gray-500 text-sm mt-1">Ajustes generales de la plataforma</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-50">

            {{-- Avatar requerido --}}
            <div class="flex items-center justify-between p-6">
                <div class="flex-1 pr-8">
                    <p class="font-semibold text-gray-900 text-sm">Foto de perfil obligatoria</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Cuando está activo, los nuevos usuarios deben elegir un avatar o subir una foto al registrarse antes de poder usar el sitio.
                    </p>
                </div>
                <div x-data="{ on: {{ $settings['avatar_required'] === '1' ? 'true' : 'false' }} }">
                    <input type="hidden" name="avatar_required" :value="on ? '1' : '0'">
                    <button type="button" @click="on = !on"
                            :class="on ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                              class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
            </div>

            {{-- Módulo de reseñas --}}
            <div class="flex items-center justify-between p-6">
                <div class="flex-1 pr-8">
                    <p class="font-semibold text-gray-900 text-sm">Módulo de reseñas</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Cuando está desactivado, las reseñas no se muestran en las propiedades ni los usuarios pueden dejar nuevas reseñas.
                    </p>
                </div>
                <div x-data="{ on: {{ $settings['reviews_enabled'] === '1' ? 'true' : 'false' }} }">
                    <input type="hidden" name="reviews_enabled" :value="on ? '1' : '0'">
                    <button type="button" @click="on = !on"
                            :class="on ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                              class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
            </div>

            {{-- Módulo de suscripción --}}
            <div class="flex items-center justify-between p-6">
                <div class="flex-1 pr-8">
                    <p class="font-semibold text-gray-900 text-sm">Módulo de suscripción</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Cuando está desactivado, todos los propietarios tienen acceso completo sin necesidad de pagar. Los bloqueos, banners y restricciones de contacto no se muestran.
                    </p>
                </div>
                <div x-data="{ on: {{ $settings['subscription_enabled'] === '1' ? 'true' : 'false' }} }">
                    <input type="hidden" name="subscription_enabled" :value="on ? '1' : '0'">
                    <button type="button" @click="on = !on"
                            :class="on ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                              class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
            </div>

            {{-- Precio suscripción propietario --}}
            <div class="flex items-center justify-between p-6">
                <div class="flex-1 pr-8">
                    <p class="font-semibold text-gray-900 text-sm">Precio de suscripción (propietarios)</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Monto en ARS que se cobra una única vez cuando el propietario activa su cuenta. Se aplica a nuevos pagos; no afecta suscripciones ya activas.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-sm font-semibold text-gray-500">$</span>
                    <input type="number" name="subscription_price" min="1" step="1"
                           value="{{ $settings['subscription_price'] }}"
                           class="w-28 px-3 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-right">
                    <span class="text-sm text-gray-400">ARS</span>
                </div>
            </div>

            {{-- Descuento suscripción --}}
            <div class="p-6">
                <p class="font-semibold text-gray-900 text-sm mb-1">Descuento en suscripción</p>
                <p class="text-xs text-gray-500 mb-4">
                    Si el descuento es mayor a 0, se muestra el precio tachado y el precio final en los banners y en la página de pago.
                    Dejá en 0 para no aplicar descuento.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex items-center gap-2">
                        <input type="number" name="subscription_discount" min="0" max="100" step="1"
                               value="{{ $settings['subscription_discount'] }}"
                               class="w-20 px-3 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-right">
                        <span class="text-sm text-gray-400">% de descuento</span>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="subscription_discount_label" maxlength="60"
                               value="{{ $settings['subscription_discount_label'] }}"
                               placeholder="Motivo (ej: Lanzamiento, Promo verano...)"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                </div>
                @php
                    $dsc  = (int) $settings['subscription_discount'];
                    $base = (int) $settings['subscription_price'];
                @endphp
                @if($dsc > 0)
                <p class="text-xs text-green-600 mt-2 font-medium">
                    Precio final actual: ${{ number_format(max(1, (int) round($base * (1 - $dsc / 100))), 0, ',', '.') }} ARS
                    (precio base ${{ number_format($base, 0, ',', '.') }} con {{ $dsc }}% off)
                </p>
                @endif
            </div>

        </div>

        <div class="mt-6">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm shadow-sm">
                Guardar cambios
            </button>
        </div>
    </form>
</div>

@endsection
