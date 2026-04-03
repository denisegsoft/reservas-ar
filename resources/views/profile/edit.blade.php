@extends('layouts.main')
@section('title', 'Mis Datos')
@section('sidebar') @include('components.user-sidebar') @endsection

@push('styles')
<style>
@keyframes alertIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.alert-in { animation: alertIn .25s ease-out forwards; }
@keyframes spin { to { transform: rotate(360deg); } }
.btn-spinner { animation: spin .7s linear infinite; }
</style>
@endpush

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
    @include('components.subscription-alert')
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Mis Datos</h1>

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
                    <p class="font-medium text-gray-700">{{ $user->full_name }}</p>
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

@push('scripts')
<script>
// ── Inline alert ──────────────────────────────────────────────
function showAlert(alertId, msg, type) {
    const el = document.getElementById(alertId);
    const isSuccess = type === 'success';
    el.className = 'alert-in flex items-center gap-3 rounded-xl px-4 py-3 text-sm border ' +
        (isSuccess
            ? 'bg-green-50 text-green-800 border-green-200'
            : 'bg-red-50 text-red-800 border-red-200');
    el.innerHTML = `
        <span class="shrink-0 text-base">${isSuccess ? '✓' : '✕'}</span>
        <span class="flex-1">${msg}</span>
        <button type="button" onclick="closeAlert('${alertId}')" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity leading-none text-lg">&times;</button>
    `;
    el.classList.remove('hidden');
}

function closeAlert(alertId) {
    const el = document.getElementById(alertId);
    el.classList.add('hidden');
}

// ── Field errors ──────────────────────────────────────────────
function showFieldErrors(form, errors) {
    form.querySelectorAll('[id^="err_"]').forEach(el => {
        el.textContent = '';
        el.classList.add('hidden');
        const field = form.querySelector(`[name="${el.id.slice(4)}"]`);
        if (field) field.classList.remove('border-red-400');
    });
    Object.entries(errors).forEach(([field, msgs]) => {
        const el    = document.getElementById('err_' + field);
        const input = form.querySelector(`[name="${field}"]`);
        if (el) { el.textContent = msgs[0]; el.classList.remove('hidden'); }
        if (input) input.classList.add('border-red-400');
    });
}

function clearFieldErrors(form) {
    form.querySelectorAll('[id^="err_"]').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
    form.querySelectorAll('input').forEach(el => el.classList.remove('border-red-400'));
}

// ── Spinner ───────────────────────────────────────────────────
function setBusy(btnId, spinnerId, busy) {
    const btn = document.getElementById(btnId);
    const sp  = document.getElementById(spinnerId);
    btn.disabled = busy;
    sp.classList.toggle('hidden', !busy);
}

// ── Profile info form ─────────────────────────────────────────
document.getElementById('profileInfoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    setBusy('profileInfoBtn', 'profileInfoSpinner', true);
    clearFieldErrors(this);
    closeAlert('profileInfoAlert');

    const fd = new FormData(this);
    fd.append('_method', 'PATCH');

    try {
        const res  = await fetch('{{ route('profile.update') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') },
            body: fd,
        });
        const data = await res.json();

        if (res.ok) {
            showAlert('profileInfoAlert', data.message, 'success');
            if (data.avatar_url) {
                document.querySelectorAll('img[data-avatar]').forEach(img => img.src = data.avatar_url);
            }
        } else if (res.status === 422) {
            showFieldErrors(this, data.errors ?? {});
            showAlert('profileInfoAlert', 'Revisá los errores del formulario.', 'error');
        } else {
            showAlert('profileInfoAlert', 'Ocurrió un error. Intentá de nuevo.', 'error');
        }
    } catch {
        showAlert('profileInfoAlert', 'Error de conexión. Intentá de nuevo.', 'error');
    } finally {
        setBusy('profileInfoBtn', 'profileInfoSpinner', false);
    }
});

// ── Password form ─────────────────────────────────────────────
const pwdMessages = {
    'validation.current_password': 'La contraseña actual es incorrecta.',
    'validation.min.string':       'La nueva contraseña debe tener al menos 8 caracteres.',
    'validation.confirmed':        'Las contraseñas no coinciden.',
    'validation.required':         'Este campo es obligatorio.',
};

function translateErrors(errors) {
    const out = {};
    Object.entries(errors).forEach(([field, msgs]) => {
        out[field] = msgs.map(m => pwdMessages[m] ?? m);
    });
    return out;
}

document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    setBusy('passwordBtn', 'passwordSpinner', true);
    clearFieldErrors(this);
    closeAlert('passwordAlert');

    const fd = new FormData(this);
    fd.append('_method', 'PUT');

    try {
        const res  = await fetch('{{ route('password.update') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') },
            body: fd,
        });
        const data = await res.json();

        if (res.ok) {
            showAlert('passwordAlert', data.message, 'success');
            this.reset();
        } else if (res.status === 422) {
            showFieldErrors(this, translateErrors(data.errors ?? {}));
            showAlert('passwordAlert', 'Revisá los errores.', 'error');
        } else {
            showAlert('passwordAlert', 'Ocurrió un error. Intentá de nuevo.', 'error');
        }
    } catch {
        showAlert('passwordAlert', 'Error de conexión. Intentá de nuevo.', 'error');
    } finally {
        setBusy('passwordBtn', 'passwordSpinner', false);
    }
});

// ── Delete account form ───────────────────────────────────────
document.getElementById('deleteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    setBusy('deleteBtn', 'deleteSpinner', true);
    document.getElementById('err_del_password').classList.add('hidden');
    closeAlert('deleteAlert');

    const fd = new FormData(this);

    try {
        const res  = await fetch('{{ route('profile.destroy') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') },
            body: new URLSearchParams({ _method: 'DELETE', _token: fd.get('_token'), password: fd.get('password') }),
        });
        const data = await res.json();

        if (res.ok) {
            window.location.href = data.redirect ?? '/';
        } else if (res.status === 422) {
            const msg = data.errors?.password?.[0] ?? 'Contraseña incorrecta.';
            const err = document.getElementById('err_del_password');
            err.textContent = msg;
            err.classList.remove('hidden');
            document.getElementById('del_password').classList.add('border-red-400');
        } else {
            showAlert('deleteAlert', 'Ocurrió un error. Intentá de nuevo.', 'error');
        }
    } catch {
        showAlert('deleteAlert', 'Error de conexión. Intentá de nuevo.', 'error');
    } finally {
        setBusy('deleteBtn', 'deleteSpinner', false);
    }
});
</script>
@endpush
