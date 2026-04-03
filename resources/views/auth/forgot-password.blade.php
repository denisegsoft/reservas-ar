<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Recuperar contrasena</h2>
        <p class="text-sm text-gray-500 mt-1">Te enviamos un enlace para restablecer tu contrasena.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Correo electronico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus placeholder="tu@email.com"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all bg-white text-gray-900 @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm">
            Enviar enlace de recuperacion
        </button>

        <p class="text-center text-sm text-gray-500 pt-2">
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                Volver al login
            </a>
        </p>
    </form>
</x-guest-layout>
