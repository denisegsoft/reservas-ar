<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Confirma tu identidad</h2>
        <p class="text-sm text-gray-500 mt-1">Esta es una area segura. Por favor confirma tu contrasena para continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Contrasena</label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password" placeholder="••••••••"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all bg-white text-gray-900 @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm">
            Confirmar
        </button>
    </form>
</x-guest-layout>
