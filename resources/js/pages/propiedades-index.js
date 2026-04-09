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

// ── Sort selects ─────────────────────────────────────────────────────────────
['sort-select', 'sort-select-mobile'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        const val = this.value;
        params.set('sort', val === 'price' ? 'price_asc' : val === 'rating' ? 'rating_asc' : val);
        window.location.href = window.location.pathname + '?' + params.toString();
    });
});
