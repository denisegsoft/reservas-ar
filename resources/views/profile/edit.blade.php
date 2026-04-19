@extends('layouts.main')
@section('title', 'Mis Datos')
@section('sidebar') @include('components.user-sidebar') @endsection

@push('styles')
@vite(['resources/css/pages/profile-edit.css'])
@endpush

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
    @include('components.subscription-alert')
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center gap-2 mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Mis Datos</h1>
        @if(auth()->user()->hasSubscription())
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        <small class="text-amber-500 font-medium text-sm">Suscripción activada</small>
        @endif
    </div>

    {{-- Avatar & Info --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Foto de perfil e información personal</h2>

        <form id="profileInfoForm" class="space-y-6">
            @csrf

            {{-- Avatar preview + upload --}}
            <div x-data="{
                preview: '{{ $user->avatar_url }}',
                change(e) {
                    const f = e.target.files[0];
                    if (f) this.preview = URL.createObjectURL(f);
                }
            }" class="flex items-center gap-6">
                <div class="relative flex-shrink-0">
                    <img :src="preview" alt="Avatar" id="avatarPreview" class="w-24 h-24 rounded-full object-cover ring-4 ring-indigo-100">
                    <label for="avatar_file" class="absolute bottom-0 right-0 w-8 h-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors" title="Cambiar foto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                    <input id="avatar_file" name="avatar_file" type="file" accept="image/*" class="sr-only" @change="change($event)">
                </div>
                <div class="text-sm text-gray-500">
                    <p data-fullname class="font-medium text-gray-700">{{ $user->full_name }}</p>
                    <p class="mt-0.5">Haz clic en el ícono de cámara para cambiar tu foto.</p>
                    <p class="mt-0.5">Máximo 3 MB. Formatos: JPG, PNG, GIF, WebP.</p>
                    <p id="err_avatar_file" class="mt-1 text-red-600 hidden"></p>
                </div>
            </div>

            {{-- Name --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input id="name" name="name" type="text"
                           value="{{ $user->name }}"
                           class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           required autofocus autocomplete="given-name">
                    <p id="err_name" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input id="last_name" name="last_name" type="text"
                           value="{{ $user->last_name }}"
                           class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           required autocomplete="family-name">
                    <p id="err_last_name" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ $user->email }}"
                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       required autocomplete="username">
                <p id="err_email" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>

            {{-- Teléfono --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <x-phone-input :value="$user->phone ?? ''" error-id="err_phone" />
            </div>

            {{-- WhatsApp --}}
            @php
                $waDefault = $user->whatsapp_link
                    ?: ($user->phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $user->phone) : '');
            @endphp
            <div class="space-y-3">
                <div>
                    <label for="whatsapp_link" class="block text-sm font-medium text-gray-700 mb-1">Link de WhatsApp</label>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <input id="whatsapp_link" name="whatsapp_link" type="text"
                               value="{{ $waDefault }}"
                               placeholder="https://wa.me/5491112345678"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Ej: https://wa.me/5491112345678 (código de país sin el +)</p>
                    <p id="err_whatsapp_link" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                @if(!auth()->user()->hasSubscription())
                <p class="text-xs text-gray-400">
                    ¿Necesitas un Chatbot inteligente para tu WhatsApp Business?
                    <button type="button"
                        onclick="profileRequestService('{{ route('profile.request-whatsapp') }}', 'whatsapp')"
                        class="font-medium underline text-green-600 hover:text-green-800 transition-colors">
                        Haz clic aquí y te ayudamos
                    </button>
                </p>
                @endif
            </div>


            {{-- Redes sociales --}}
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700">Redes sociales</p>

                @php
                $socials = [
                    ['key' => 'social_instagram', 'label' => 'Instagram',  'placeholder' => 'https://instagram.com/usuario',
                     'color' => '#e1306c',
                     'icon'  => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>'],
                    ['key' => 'social_facebook',  'label' => 'Facebook',   'placeholder' => 'https://facebook.com/usuario',
                     'color' => '#1877f2',
                     'icon'  => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>'],
                    ['key' => 'social_twitter',   'label' => 'X / Twitter', 'placeholder' => 'https://x.com/usuario',
                     'color' => '#000000',
                     'icon'  => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.259 5.626L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>'],
                    ['key' => 'social_tiktok',    'label' => 'TikTok',     'placeholder' => 'https://tiktok.com/@usuario',
                     'color' => '#010101',
                     'icon'  => '<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.17 8.17 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/>'],
                    ['key' => 'social_youtube',   'label' => 'YouTube',    'placeholder' => 'https://youtube.com/@canal',
                     'color' => '#ff0000',
                     'icon'  => '<path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>'],
                    ['key' => 'website',          'label' => 'Página web', 'placeholder' => 'https://mipagina.com',
                     'color' => '#6366f1',
                     'icon'  => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>'],
                ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($socials as $s)
                    <div>
                        <label for="{{ $s['key'] }}" class="flex items-center gap-1.5 text-xs font-medium text-gray-500 mb-1">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="{{ $s['color'] }}">{!! $s['icon'] !!}</svg>
                            {{ $s['label'] }}
                        </label>
                        <input id="{{ $s['key'] }}" name="{{ $s['key'] }}" type="url"
                               value="{{ $user->{$s['key']} ?? '' }}"
                               placeholder="{{ $s['placeholder'] }}"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- CTA: solicitar web/redes --}}
            @if(!auth()->user()->hasSubscription())
            <div class="mt-1">
                <p class="text-xs text-gray-400">
                    ¿No tienes web profesional ni redes?
                    <button type="button"
                        onclick="profileRequestService('{{ route('profile.request-website') }}', 'website')"
                        class="font-medium underline text-indigo-500 hover:text-indigo-700 transition-colors">
                        Haz clic aquí si necesitas una
                    </button>
                </p>
            </div>
            @endif

            {{-- Datos bancarios --}}
            <div class="space-y-3 border-t border-gray-100 pt-5">
                <p class="text-sm font-medium text-gray-700">Datos bancarios para transferencias</p>
                <p class="text-xs text-gray-400 -mt-2">Si sos propietario, se mostrarán a los clientes en sus reservas.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="bank_holder" class="block text-xs font-medium text-gray-500 mb-1">Titular de la cuenta</label>
                        <input id="bank_holder" name="bank_holder" type="text"
                               value="{{ $user->bank_holder ?? '' }}"
                               placeholder="Nombre y apellido"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p id="err_bank_holder" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label for="bank_cbu" class="block text-xs font-medium text-gray-500 mb-1">CBU / CVU</label>
                        <input id="bank_cbu" name="bank_cbu" type="text"
                               value="{{ $user->bank_cbu ?? '' }}"
                               placeholder="22 dígitos" maxlength="22"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p id="err_bank_cbu" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label for="bank_alias" class="block text-xs font-medium text-gray-500 mb-1">Alias</label>
                        <input id="bank_alias" name="bank_alias" type="text"
                               value="{{ $user->bank_alias ?? '' }}"
                               placeholder="mi.alias.banco"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p id="err_bank_alias" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                </div>
            </div>

            {{-- Alert --}}
            <div id="profileInfoAlert" class="hidden"></div>

            <div>
                <button type="submit" id="profileInfoBtn"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    <svg id="profileInfoSpinner" class="hidden btn-spinner w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="rgba(255,255,255,.3)" stroke-width="3"/>
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span>Guardar cambios</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Change password --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Cambiar contraseña</h2>

        <form id="passwordForm" class="space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
                <input id="current_password" name="current_password" type="password"
                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       autocomplete="current-password">
                <p id="err_current_password" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                <input id="new_password" name="password" type="password"
                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       autocomplete="new-password">
                <p id="err_password" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       autocomplete="new-password">
                <p id="err_password_confirmation" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>

            {{-- Alert --}}
            <div id="passwordAlert" class="hidden"></div>

            <div>
                <button type="submit" id="passwordBtn"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    <svg id="passwordSpinner" class="hidden btn-spinner w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="rgba(255,255,255,.3)" stroke-width="3"/>
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span>Actualizar contraseña</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Delete account --}}
    <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6 sm:p-8"
         x-data="{ open: false }">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Eliminar cuenta</h2>
        <p class="text-sm text-gray-500 mb-5">Una vez eliminada, toda tu información será borrada permanentemente.</p>

        <button type="button" @click="open=true"
                class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
            Eliminar mi cuenta
        </button>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10" @click.stop>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">¿Eliminar tu cuenta?</h3>
                <p class="text-sm text-gray-500 mb-6">Esta acción es irreversible. Ingresa tu contraseña para confirmar.</p>

                <form id="deleteForm" class="space-y-4">
                    @csrf
                    <div>
                        <label for="del_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input id="del_password" name="password" type="password"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Tu contraseña actual">
                        <p id="err_del_password" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div id="deleteAlert" class="hidden"></div>

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" @click="open=false"
                                class="text-sm font-medium text-gray-700 hover:text-gray-900 px-4 py-2.5 rounded-xl border border-gray-300 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" id="deleteBtn"
                                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                            <svg id="deleteSpinner" class="hidden btn-spinner w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="rgba(255,255,255,.3)" stroke-width="3"/>
                                <path d="M12 3a9 9 0 0 1 9 9" stroke="white" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span>Sí, eliminar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- Modal servicios adicionales --}}
<div class="modal fade" id="modalSuscripcionRequerida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0" style="border-radius:1.75rem;overflow:hidden;box-shadow:0 32px 64px -12px rgba(0,0,0,.3);">

            {{-- Header gradiente --}}
            <div style="position:relative;padding:1.75rem 1.5rem 1.5rem;background:linear-gradient(135deg,#4338ca 0%,#6d28d9 55%,#9333ea 100%);overflow:hidden;">
                {{-- Círculos decorativos --}}
                <div style="position:absolute;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.08);top:-28px;right:-28px;pointer-events:none;"></div>
                <div style="position:absolute;width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.1);top:16px;right:52px;pointer-events:none;"></div>
                {{-- Cerrar --}}
                <button type="button" data-bs-dismiss="modal"
                        style="position:absolute;top:14px;right:14px;width:30px;height:30px;background:rgba(255,255,255,.2);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                    <svg width="13" height="13" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                {{-- Ícono + título --}}
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div id="modalSuscripcionIcon"
                         style="width:54px;height:54px;border-radius:1rem;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.65rem;flex-shrink:0;">💬</div>
                    <div style="min-width:0;">
                        <div style="font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:.3rem;">Servicio premium</div>
                        <div id="modalSuscripcionTitle" style="font-size:1.15rem;font-weight:900;color:#fff;line-height:1.2;">Servicio</div>
                    </div>
                </div>
            </div>

            {{-- Cuerpo --}}
            <div style="padding:1.4rem 1.5rem 1.5rem;">
                <p id="modalSuscripcionDesc" style="font-size:.875rem;color:#6b7280;line-height:1.6;margin-bottom:1.2rem;">
                    Para acceder a este servicio necesitás activar tu suscripción.
                </p>

                {{-- Beneficios --}}
                <div id="modalSuscripcionBenefits" style="display:flex;flex-direction:column;gap:.65rem;margin-bottom:1.4rem;"></div>

                {{-- Precio + CTA --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.1rem;border-radius:1.1rem;background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1px solid #e0e7ff;">
                    <div>
                        <div style="font-size:.7rem;color:#9ca3af;margin-bottom:.2rem;">Pago único · Sin renovaciones</div>
                        @if($subscriptionDiscount)
                        <div style="font-size:1rem;font-weight:700;color:#f87171;text-decoration:line-through;line-height:1.2;">
                            ${{ number_format($subscriptionBasePrice, 0, ',', '.') }} ARS
                        </div>
                        @endif
                        <div style="font-size:1.7rem;font-weight:900;color:#111827;line-height:1;">
                            ${{ number_format($subscriptionPrice, 0, ',', '.') }}
                            <span style="font-size:.85rem;font-weight:400;color:#9ca3af;">ARS</span>
                        </div>
                        @if($subscriptionDiscount)
                        <div style="display:inline-block;margin-top:.3rem;font-size:.68rem;font-weight:700;background:#dcfce7;color:#15803d;padding:.15rem .5rem;border-radius:20px;">
                            🏷️ {{ $subscriptionDiscount['pct'] }}% OFF{{ $subscriptionDiscount['label'] ? ' · ' . $subscriptionDiscount['label'] : '' }}
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('subscription.payment') }}"
                       style="flex-shrink:0;display:inline-flex;align-items:center;gap:.4rem;color:#fff;font-weight:700;font-size:.875rem;padding:.7rem 1.2rem;border-radius:.875rem;background:linear-gradient(135deg,#4338ca,#6d28d9);box-shadow:0 6px 20px rgba(99,102,241,.35);white-space:nowrap;text-decoration:none;"
                       onmouseover="this.style.boxShadow='0 8px 25px rgba(99,102,241,.5)'" onmouseout="this.style.boxShadow='0 6px 20px rgba(99,102,241,.35)'">
                        Activar ahora
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                {{-- Seguridad --}}
                <div style="display:flex;align-items:center;justify-content:center;gap:.35rem;margin-top:.75rem;font-size:.72rem;color:#9ca3af;">
                    <svg width="11" height="11" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    Pago seguro vía MercadoPago
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
window.PROFILE_ROUTE_UPDATE          = '{{ route('profile.update') }}';
window.PROFILE_ROUTE_PASSWORD_UPDATE = '{{ route('password.update') }}';
window.PROFILE_ROUTE_DESTROY         = '{{ route('profile.destroy') }}';

const CSRF = document.querySelector('meta[name=csrf-token]')?.content ?? '';

const SERVICE_DATA = {
    whatsapp: {
        icon:        '💬',
        title:       'Chatbot inteligente para tu WhatsApp',
        description: '¿Querés que tu negocio responda automáticamente las 24 hs? Te ayudamos a configurar un Chatbot inteligente  para tu WhatsApp Business. Además, te creamos un sitio web profesional para que tu negocio tenga presencia online.',
        benefits: [
            { emoji: '🤖', bg: '#dcfce7', text: '<strong>Respuestas automáticas</strong> para consultas frecuentes' },
            { emoji: '🕐', bg: '#dbeafe', text: 'Atención <strong>24/7</strong>, incluso cuando no estás disponible' },
            { emoji: '📲', bg: '#e0e7ff', text: 'Gestión de <strong>reservas y consultas</strong> sin esfuerzo' },
            { emoji: '📈', bg: '#f3e8ff', text: 'Más conversiones con <strong>seguimiento automático</strong>' },
        ],
    },
    website: {
        icon:        '🌐',
        title:       'Web profesional + Redes',
        description: '¿Todavía no tenés presencia online? Te creamos un sitio profesional para hacer crecer tu negocio. También incluye un Chatbot inteligente para tu WhatsApp Business para que ahorres tu tiempo y atiendas clientes las 24 hs.',
        benefits: [
            { emoji: '🎨', bg: '#e0e7ff', text: '<strong>Sitio web</strong> profesional a medida de tu negocio' },
            { emoji: '🤖', bg: '#dcfce7', text: 'Reservas 24/7 con el Chatbot inteligente para Whatsapp' },
            { emoji: '📸', bg: '#ffedd5', text: 'Unificamos y potenciamos el manejo de tus redes sociales' },
            { emoji: '🚀', bg: '#dcfce7', text: 'Mayor visibilidad y <strong>más reservas</strong>' },
        ],
    },
};

async function profileRequestService(url, type) {
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).catch(() => {});

    const data = SERVICE_DATA[type] ?? SERVICE_DATA.website;

    document.getElementById('modalSuscripcionIcon').textContent  = data.icon;
    document.getElementById('modalSuscripcionTitle').textContent = data.title;
    document.getElementById('modalSuscripcionDesc').textContent  = data.description;

    const list = document.getElementById('modalSuscripcionBenefits');
    list.innerHTML = data.benefits.map(b => `
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-xl flex-shrink-0 text-base"
                 style="width:32px;height:32px;background:${b.bg};">${b.emoji}</div>
            <p class="text-sm text-gray-700 mb-0">${b.text}</p>
        </div>
    `).join('');

    new bootstrap.Modal(document.getElementById('modalSuscripcionRequerida')).show();
}
</script>
@vite(['resources/js/pages/profile-edit.js'])
@endpush
