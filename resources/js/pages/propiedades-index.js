// ── Auto-submit filters ───────────────────────────────────────────────────────
window.filtersAutoSubmit = function (form) {
    if (!form) return;
    const data = new FormData(form);
    const params = new URLSearchParams();
    for (const [key, value] of data.entries()) {
        if (value !== '') params.append(key, value);
    }
    // preserve current sort
    const currentSort = new URLSearchParams(window.location.search).get('sort');
    if (currentSort && !params.has('sort')) params.set('sort', currentSort);
    window.location.href = form.action + '?' + params.toString();
};

let _debounceTimer;
function _debounceSubmit(form) {
    clearTimeout(_debounceTimer);
    _debounceTimer = setTimeout(() => window.filtersAutoSubmit(form), 600);
}

function _bindForm(form) {
    if (!form) return;
    form.querySelectorAll('select, input[type="radio"], input[type="checkbox"]').forEach(el => {
        el.addEventListener('change', () => window.filtersAutoSubmit(form));
    });
    form.querySelectorAll('input[type="number"], input[type="text"]').forEach(el => {
        if (['price_min', 'price_max', 'guests'].includes(el.name)) return;
        el.addEventListener('input', () => _debounceSubmit(form));
    });
}

_bindForm(document.getElementById('filter-form'));
_bindForm(document.querySelector('#filtrosModal form'));

// ── Cascading geo selects ─────────────────────────────────────────────────────
function populateSelect(select, options, emptyLabel) {
    const current = select.value;
    select.innerHTML = `<option value="">${emptyLabel}</option>`;
    options.forEach(opt => {
        const name = typeof opt === 'string' ? opt : opt.name;
        const el = document.createElement('option');
        el.value = name;
        el.textContent = name;
        if (name === current) el.selected = true;
        select.appendChild(el);
    });
}

function bindGeoSelects(stateId, partidoId, localityId) {
    const stateEl   = document.getElementById(stateId);
    const partidoEl = document.getElementById(partidoId);
    const localityEl= document.getElementById(localityId);
    if (!stateEl || !partidoEl || !localityEl) return;

    stateEl.addEventListener('change', function () {
        partidoEl.innerHTML  = '<option value="">Todos los partidos</option>';
        localityEl.innerHTML = '<option value="">Todas las localidades</option>';
        if (!this.value) return;
        fetch(`/geo/partidos?province=${encodeURIComponent(this.value)}`)
            .then(r => r.json())
            .then(data => populateSelect(partidoEl, data, 'Todos los partidos'));
    });

    partidoEl.addEventListener('change', function () {
        localityEl.innerHTML = '<option value="">Todas las localidades</option>';
        if (!this.value) return;
        // find partido id from current options
        const selected = Array.from(partidoEl.options).find(o => o.value === this.value);
        const partidoId = selected?.dataset?.id;
        if (!partidoId) {
            // fetch by name: get partido id first
            fetch(`/geo/partidos?province=${encodeURIComponent(stateEl.value)}`)
                .then(r => r.json())
                .then(data => {
                    const found = data.find(p => p.name === this.value);
                    if (found) fetchLocalidades(found.id, localityEl);
                });
        } else {
            fetchLocalidades(partidoId, localityEl);
        }
    });
}

function fetchLocalidades(partidoId, localityEl) {
    fetch(`/geo/localidades?partido_id=${partidoId}`)
        .then(r => r.json())
        .then(data => populateSelect(localityEl, data, 'Todas las localidades'));
}

// Al cargar la página, si hay partido seleccionado, precargar localidades
function preloadLocalidades(stateId, partidoId, localityId) {
    const stateEl    = document.getElementById(stateId);
    const partidoEl  = document.getElementById(partidoId);
    const localityEl = document.getElementById(localityId);
    if (!stateEl || !partidoEl || !localityEl) return;
    if (!stateEl.value || !partidoEl.value) return;

    fetch(`/geo/partidos?province=${encodeURIComponent(stateEl.value)}`)
        .then(r => r.json())
        .then(data => {
            const found = data.find(p => p.name === partidoEl.value);
            if (found) fetchLocalidades(found.id, localityEl);
        });
}

bindGeoSelects('sidebar-state', 'sidebar-partido', 'sidebar-locality');
bindGeoSelects('modal-state',   'modal-partido',   'modal-locality');
preloadLocalidades('sidebar-state', 'sidebar-partido', 'sidebar-locality');
preloadLocalidades('modal-state',   'modal-partido',   'modal-locality');

// ── Sort selects ─────────────────────────────────────────────────────────────
['sort-select', 'sort-select-mobile'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        const val = this.value;
        params.set('sort', val === 'price' ? 'price_asc' : val === 'rating' ? 'rating_asc' : val);
        window.location.href = window.location.pathname + '?' + params.toString();
    });
});
