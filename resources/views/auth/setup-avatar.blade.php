<x-guest-layout>
    <div class="mb-8">
        <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Elegí tu foto de perfil</h2>
        <p class="text-sm text-gray-500 mt-1">Seleccioná un avatar o subí tu propia foto para completar tu cuenta.</p>
    </div>

    @php
        $presets = [
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Aneka',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Buster',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Cleo',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Felix',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Luna',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Max',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Nala',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Oreo',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Salem',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Gracie',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Tigger',
            'https://api.dicebear.com/9.x/adventurer/svg?seed=Zoe',
        ];
        $defaultPreset = old('avatar_preset', $presets[0]);
    @endphp

    <form method="POST" action="{{ route('avatar.store') }}" enctype="multipart/form-data"
          x-data="{
              tab: '{{ old('avatar_type', 'preset') }}',
              selectedPreset: '{{ $defaultPreset }}',
              previewUrl: null,
              handleFile(e) {
                  const file = e.target.files[0];
                  if (file) this.previewUrl = URL.createObjectURL(file);
              }
          }">
        @csrf

        {{-- Tabs --}}
        <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-4">
            <button type="button" @click="tab = 'preset'"
                    :class="tab === 'preset' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all">
                Elegir avatar
            </button>
            <button type="button" @click="tab = 'upload'"
                    :class="tab === 'upload' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all">
                Subir foto
            </button>
        </div>

        {{-- Preset avatars --}}
        <div x-show="tab === 'preset'" class="grid grid-cols-4 gap-3 mb-6">
            @foreach($presets as $url)
            <button type="button" @click="selectedPreset = '{{ $url }}'"
                    :class="selectedPreset === '{{ $url }}' ? 'ring-2 ring-indigo-500 ring-offset-2 scale-105' : 'ring-1 ring-gray-200 hover:ring-indigo-300 hover:scale-105'"
                    class="rounded-full overflow-hidden w-full aspect-square transition-all bg-indigo-50 shadow-sm">
                <img src="{{ $url }}" alt="Avatar" class="w-full h-full object-cover">
            </button>
            @endforeach
        </div>

        {{-- File upload --}}
        <div x-show="tab === 'upload'" class="mb-6">
            <div class="flex flex-col items-center gap-4">
                <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center border-2 border-gray-200">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!previewUrl">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </template>
                </div>
                <label class="cursor-pointer flex items-center gap-2 px-5 py-2.5 border border-dashed border-gray-300 rounded-xl text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-text="previewUrl ? 'Cambiar imagen' : 'Seleccionar imagen'"></span>
                    <input type="file" name="avatar_file" accept="image/*" class="hidden" @change="handleFile($event)">
                </label>
                <p class="text-xs text-gray-400">JPG, PNG o GIF · máx. 3 MB</p>
            </div>
        </div>

        {{-- Hidden inputs --}}
        <input type="hidden" name="avatar_type" :value="tab">
        <input type="hidden" name="avatar_preset" :value="selectedPreset">

        @error('avatar_preset')
            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
        @enderror
        @error('avatar_file')
            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 hover:shadow-indigo-300 text-sm">
            Continuar
        </button>
    </form>
</x-guest-layout>
