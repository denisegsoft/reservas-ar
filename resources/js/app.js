import './bootstrap';

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

Alpine.start();
