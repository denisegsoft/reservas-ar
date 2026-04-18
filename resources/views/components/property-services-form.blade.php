{{--
    Sección de servicios adicionales para crear/editar propiedad.
    $existingServices — colección de PropertyService (solo en edit)
--}}
@props(['existingServices' => collect()])

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6"
     x-data="{
        services: @js($existingServices->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => $s->price, 'quantity' => $s->quantity, 'unit' => $s->unit])->values()),
        newName: '', newPrice: '', newQty: 1, newUnit: 'unidad',
        units: ['unidad','hora','día','persona','kg','litro'],
        add() {
            if (!this.newName.trim() || this.newPrice === '') return;
            this.services.push({ id: null, name: this.newName.trim(), price: parseFloat(this.newPrice), quantity: parseFloat(this.newQty) || 1, unit: this.newUnit });
            this.newName = ''; this.newPrice = ''; this.newQty = 1; this.newUnit = 'unidad';
        },
        remove(i) { this.services.splice(i, 1); }
     }">
    <h2 class="text-lg font-bold text-gray-900 mb-1">Servicios adicionales</h2>
    <p class="text-sm text-gray-400 mb-5">Servicios opcionales que el cliente puede agregar a su reserva.</p>

    {{-- Lista existente --}}
    <div class="space-y-2 mb-4">
        <template x-if="services.length === 0">
            <p class="text-sm text-gray-400 italic">Sin servicios cargados.</p>
        </template>
        <template x-for="(s, i) in services" :key="i">
            <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-4 py-3">
                <div class="flex-1 min-w-0">
                    <span class="text-sm font-semibold text-gray-800" x-text="s.name"></span>
                    <span class="text-xs text-gray-400 ml-2" x-text="`$${parseFloat(s.price).toLocaleString('es-AR')} × ${s.quantity} ${s.unit}`"></span>
                </div>
                <button type="button" @click="remove(i)" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                {{-- Campos ocultos --}}
                <input type="hidden" :name="`services[${i}][id]`" :value="s.id ?? ''">
                <input type="hidden" :name="`services[${i}][name]`" :value="s.name">
                <input type="hidden" :name="`services[${i}][price]`" :value="s.price">
                <input type="hidden" :name="`services[${i}][quantity]`" :value="s.quantity">
                <input type="hidden" :name="`services[${i}][unit]`" :value="s.unit">
            </div>
        </template>
    </div>

    {{-- Agregar nuevo --}}
    <div class="border border-dashed border-gray-200 rounded-xl p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Agregar servicio</p>
        <div class="flex flex-col sm:flex-row gap-3 mb-3">
            <input type="text" x-model="newName" placeholder="Nombre (ej: Limpieza final)"
                   class="flex-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
            <div class="grid grid-cols-3 sm:flex sm:gap-2 gap-2">
                <input type="number" x-model="newPrice" placeholder="Precio $" min="0" step="0.01"
                       class="sm:w-32 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                <input type="number" x-model="newQty" placeholder="Cant." min="0.01" step="0.01"
                       class="sm:w-20 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                <select x-model="newUnit" class="sm:w-28 px-2 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    <template x-for="u in units" :key="u">
                        <option :value="u" x-text="u"></option>
                    </template>
                </select>
            </div>
        </div>
        <button type="button" @click="add()"
                class="flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Agregar
        </button>
    </div>
</div>
