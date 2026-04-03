@extends('layouts.main')
@section('title', 'Sugerencias')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Sugerencias</h1>
        <p class="text-gray-500 text-sm mt-1">¿Tenés una idea para mejorar la plataforma? Nos interesa escucharte..</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl px-5 py-4 mb-6 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <form action="{{ route('suggestions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="150"
                           placeholder="Ponle un título a tu pedido"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="6" required minlength="10" maxlength="2000"
                              placeholder="Describe tu solicitud...."
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adjuntos <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <div x-data="{ files: [] }"
                         class="border-2 border-dashed border-gray-200 rounded-xl p-5 text-center hover:border-indigo-300 transition-colors">
                        <input type="file" name="attachments[]" multiple id="attachments-input"
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip"
                               @change="files = Array.from($event.target.files)"
                               class="hidden">
                        <label for="attachments-input" class="cursor-pointer">
                            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <p class="text-sm text-gray-500">Hacé clic para adjuntar archivos</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF, DOC, ZIP · Máx. 5 MB por archivo</p>
                        </label>
                        <template x-if="files.length > 0">
                            <ul class="mt-3 space-y-1 text-left">
                                <template x-for="f in files" :key="f.name">
                                    <li class="flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-1.5">
                                        <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span x-text="f.name" class="truncate"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>
                    </div>
                    @error('attachments.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-7" style="margin-top: 20px">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-2xl transition-all shadow-lg shadow-indigo-200">
                    Enviar sugerencia
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
