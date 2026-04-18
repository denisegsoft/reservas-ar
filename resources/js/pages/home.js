(function () {
    const state    = document.getElementById('home-state');
    const partido  = document.getElementById('home-partido');
    const locality = document.getElementById('home-locality');

    if (!state || !partido || !locality) return;

    function resetSelect(sel, placeholder) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        sel.disabled = true;
    }

    function loadPartidos(provinceName) {
        resetSelect(partido, 'Todos los partidos');
        resetSelect(locality, 'Todas las localidades');
        if (!provinceName) { partido.disabled = false; return; }
        fetch('/geo/partidos?province=' + encodeURIComponent(provinceName))
            .then(r => r.json())
            .then(data => {
                partido.disabled = false;
                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.dataset.id = p.id;
                    opt.textContent = p.name;
                    partido.appendChild(opt);
                });
            });
    }

    function loadLocalidades(partidoId) {
        resetSelect(locality, 'Todas las localidades');
        if (!partidoId) { locality.disabled = false; return; }
        fetch('/geo/localidades?partido_id=' + encodeURIComponent(partidoId))
            .then(r => r.json())
            .then(data => {
                locality.disabled = false;
                data.forEach(name => {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;
                    locality.appendChild(opt);
                });
            });
    }

    partido.disabled  = true;
    locality.disabled = true;

    state.addEventListener('change', () => loadPartidos(state.value));

    partido.addEventListener('change', () => {
        const sel = partido.options[partido.selectedIndex];
        loadLocalidades(sel ? sel.dataset.id : null);
    });
})();
