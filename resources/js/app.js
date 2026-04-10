import './bootstrap';
import './components/reservas-calendar';
import './components/phone-input';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('addressAutocomplete', (initProvinceId, initPartido, initLocality) => ({
    provinceId: initProvinceId || '',
    provinceName: '',

    partidoInput: initPartido || '',
    partidoOptions: [],
    partidoFiltered: [],
    showPartidoDD: false,
    partidoId: null,
    loadingPartidos: false,

    localidadInput: initLocality || '',
    localidadOptions: [],
    localidadFiltered: [],
    showLocalidadDD: false,
    loadingLocs: false,

    async init() {
        // Sincronizar nombre de provincia desde el select al arrancar
        const sel = this.$el.querySelector('select');
        if (sel) {
            const opt = sel.options[sel.selectedIndex];
            this.provinceName = opt ? (opt.dataset.name || opt.text) : '';
        }
        if (this.provinceId) {
            await this.fetchPartidos(this.provinceId);
            if (this.partidoInput) {
                const match = this.partidoOptions.find(p => p.name === this.partidoInput);
                if (match) {
                    this.partidoId = match.id;
                    await this.fetchLocalidades(match.id);
                }
            }
        }
    },

    onProvinceChange(e) {
        this.provinceId   = e.target.value;
        this.provinceName = e.target.options[e.target.selectedIndex]?.dataset.name || '';
        this.partidoInput = '';
        this.partidoId    = null;
        this.localidadInput  = '';
        this.localidadOptions = [];
        if (this.provinceId) this.fetchPartidos(this.provinceId);
    },

    async fetchPartidos(provinceId) {
        this.loadingPartidos = true;
        try {
            const res = await fetch('/geo/partidos?province_id=' + encodeURIComponent(provinceId));
            this.partidoOptions = await res.json();
        } catch(e) {}
        this.loadingPartidos = false;
    },

    onPartidoInput() {
        const q = this.partidoInput.toLowerCase();
        this.partidoFiltered = q
            ? this.partidoOptions.filter(p => p.name.toLowerCase().includes(q)).slice(0, 10)
            : this.partidoOptions.slice(0, 10);
        this.showPartidoDD = true;
    },

    selectPartido(p) {
        this.partidoInput = p.name;
        this.partidoId = p.id;
        this.showPartidoDD = false;
        this.localidadInput = '';
        this.fetchLocalidades(p.id);
    },

    async fetchLocalidades(partidoId) {
        this.loadingLocs = true;
        try {
            const res = await fetch('/geo/localidades?partido_id=' + partidoId);
            this.localidadOptions = await res.json();
        } catch(e) {}
        this.loadingLocs = false;
    },

    onLocalidadInput() {
        const q = this.localidadInput.toLowerCase();
        this.localidadFiltered = q
            ? this.localidadOptions.filter(l => l.toLowerCase().includes(q)).slice(0, 10)
            : this.localidadOptions.slice(0, 10);
        this.showLocalidadDD = true;
    },

    selectLocalidad(l) {
        this.localidadInput = l;
        this.showLocalidadDD = false;
    },
}));

document.addEventListener('DOMContentLoaded', () => Alpine.start());

// ── Sidebar ────────────────────────────────────────────────────
(function () {
    if (document.getElementById('app-sidebar') && localStorage.getItem('sb') !== '0') {
        document.body.classList.add('sb-open');
    }
})();
window.sidebarOpen  = () => { document.body.classList.add('sb-open');    localStorage.setItem('sb', '1'); };
window.sidebarClose = () => { document.body.classList.remove('sb-open'); localStorage.setItem('sb', '0'); };

// ── Tom Select global init ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-ts]').forEach(function (el) {
        const canCreate = el.hasAttribute('data-ts-create');
        new TomSelect(el, {
            allowEmptyOption: true,
            placeholder: el.dataset.placeholder || 'Buscar...',
            maxOptions: null,
            create: canCreate,
            createOnBlur: canCreate,
            createFilter: canCreate ? function (input) { return input.trim().length > 0; } : null,
        });
    });

    // AJAX city loading para selects nativos (sin data-ts)
    document.querySelectorAll('select[name="state"]:not([data-ts])').forEach(function (stateEl) {
        const form = stateEl.closest('form');
        const cityEl = form ? form.querySelector('select[name="city"]') : null;
        if (!cityEl) return;

        function loadCities(stateName, restoreCity) {
            while (cityEl.options.length > 1) cityEl.remove(1);
            if (!stateName) return;
            fetch('/api/cities?state=' + encodeURIComponent(stateName))
                .then(r => r.json())
                .then(cities => {
                    cities.forEach(function (c) {
                        const opt = document.createElement('option');
                        opt.value = c; opt.textContent = c;
                        if (restoreCity && c === restoreCity) opt.selected = true;
                        cityEl.appendChild(opt);
                    });
                })
                .catch(function () {});
        }

        stateEl.addEventListener('change', function () { loadCities(this.value, null); });
        if (stateEl.value) loadCities(stateEl.value, cityEl.dataset.selected || null);
    });
});

// ── Alpine: customAmenities ────────────────────────────────────
window.customAmenities = function (initial) {
    return {
        items: initial || [],
        input: '',
        add() {
            const val = this.input.trim();
            if (val && !this.items.includes(val)) this.items.push(val);
            this.input = '';
        },
        remove(i) { this.items.splice(i, 1); },
    };
};
