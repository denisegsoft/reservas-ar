{{--
    Selector de servicios adicionales para una reserva.
    Props:
      $availableServices — colección de PropertyService de la propiedad
      $selectedServices  — colección de ReservationService ya guardados (para edición)
--}}
@props(['availableServices' => collect(), 'selectedServices' => collect()])

@if($availableServices->count())
<div x-data="{
        items: @js($selectedServices->map(fn($rs) => [
            'id'   => $rs->propertyService->id,
            'name' => $rs->propertyService->name,
            'unit' => $rs->propertyService->unit,
            'price'=> (float) $rs->price,
            'qty'  => (float) $rs->quantity,
        ])->values()),
        available: @js($availableServices->map(fn($s) => [
            'id'    => $s->id,
            'name'  => $s->name,
            'price' => (float) $s->price,
            'defQty'=> (float) $s->quantity,
            'unit'  => $s->unit,
        ])->values()),
        selId: '',
        selQty: 1,
        get selService() { return this.available.find(s => s.id == this.selId) ?? null; },
        get remaining() { return this.available.filter(s => !this.items.some(i => i.id === s.id)); },
        add() {
            const s = this.selService;
            if (!s) return;
            this.items.push({ id: s.id, name: s.name, unit: s.unit, price: s.price, qty: parseFloat(this.selQty) || 1 });
            this.selId = ''; this.selQty = 1;
        },
        remove(i) { this.items.splice(i, 1); },
        total() { return this.items.reduce((sum, i) => sum + i.price * i.qty, 0); },
        fmt(n) { return Math.round(n).toLocaleString('es-AR'); }
     }">

    {{-- Lista agregada --}}
    <template x-if="items.length > 0">
        <div class="space-y-2 mb-4">
            <template x-for="(item, i) in items" :key="i">
                <div class="flex items-center justify-between gap-3 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="`${item.qty} ${item.unit} · $${fmt(item.price * item.qty)}`"></p>
                    </div>
                    <button type="button" @click="remove(i)" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <div class="flex justify-between pt-1">
                <span class="text-xs text-gray-500">Total servicios</span>
                <span class="text-sm font-bold text-indigo-700" x-text="`$${fmt(total())}`"></span>
            </div>
        </div>
    </template>

    {{-- Agregar servicio --}}
    <template x-if="remaining.length > 0">
        <div class="flex gap-2 items-end">
            <div class="flex-1">
                <select x-model="selId" @change="selQty = selService?.defQty ?? 1"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    <option value="">Elegir servicio...</option>
                    <template x-for="s in remaining" :key="s.id">
                        <option :value="s.id" x-text="`${s.name} — $${fmt(s.price * s.defQty)}`"></option>
                    </template>
                </select>
            </div>
            <div class="w-24">
                <input type="number" x-model="selQty" min="0.01" step="0.01" placeholder="Cant."
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none text-center">
            </div>
            <button type="button" @click="add()" :disabled="!selId"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
                + Agregar
            </button>
        </div>
    </template>

    {{-- Hidden inputs --}}
    <template x-for="(item, i) in items" :key="i">
        <span>
            <input type="hidden" :name="`reservation_services[${i}][property_service_id]`" :value="item.id">
            <input type="hidden" :name="`reservation_services[${i}][quantity]`" :value="item.qty">
            <input type="hidden" :name="`reservation_services[${i}][price]`" :value="item.price">
        </span>
    </template>
</div>
@endif
