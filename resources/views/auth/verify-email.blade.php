<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Verifica tu email</h2>
        <p class="text-sm text-gray-500 mt-2">
            Gracias por registrarte. Te enviamos un enlace de verificacion a tu correo. Si no lo recibiste, podemos enviarte otro.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">
            Se envio un nuevo enlace de verificacion a tu correo electronico.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm">
                Reenviar email de verificacion
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-white hover:bg-gray-50 text-gray-600 font-semibold py-3 px-6 rounded-xl border border-gray-200 transition-all text-sm">
                Cerrar sesion
            </button>
        </form>
    </div>
</x-guest-layout>
