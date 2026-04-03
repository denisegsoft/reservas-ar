@extends('layouts.main')
@section('title', 'Soporte')
@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-14">

    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Soporte</h1>
        <p class="text-gray-500 text-sm mt-1">¿Tenés un problema o necesitás ayuda? Completá el formulario y te responderemos a la brevedad.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl px-5 py-4 mb-6 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <form action="{{ route('support.store') }}" method="POST">
            @csrf
            <div class="space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="150"
                               placeholder="tu@email.com"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <x-phone-input :value="old('phone', '')" :show-whatsapp="false"/>
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Asunto <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="150"
                           placeholder="Ej: No puedo iniciar sesión, Error al reservar..."
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('subject')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mensaje <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="6" required minlength="10" maxlength="3000"
                              placeholder="Describí tu problema con el mayor detalle posible..."
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('message') }}</textarea>
                    @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

            </div>

            <div class="mt-7">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-2xl transition-all shadow-lg shadow-indigo-200">
                    Enviar mensaje
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
