// ===================== AUTOCOMPLETE PARTIDO / LOCALIDAD =====================
var partidoOptions = [];
var partidoId = null;

document.getElementById('sel-province').addEventListener('change', function() {
    var provinceId = this.value;
    var provinceName = this.options[this.selectedIndex] ? (this.options[this.selectedIndex].dataset.name || '') : '';
    document.getElementById('inp-state').value = provinceName;
    var inpP = document.getElementById('input-partido');
    var inpL = document.getElementById('input-localidad');
    inpP.value = ''; inpP.disabled = !provinceId;
    inpL.value = ''; inpL.disabled = true;
    partidoOptions = []; partidoId = null;
    document.getElementById('dd-partido').innerHTML = '';
    document.getElementById('dd-localidad').innerHTML = '';
    if (!provinceId) return;
    document.getElementById('spin-partido').classList.remove('hidden');
    fetch('/geo/partidos?province_id=' + encodeURIComponent(provinceId))
        .then(function(r){ return r.json(); })
        .then(function(data){
            partidoOptions = data;
            document.getElementById('spin-partido').classList.add('hidden');
        });
});

document.getElementById('input-partido').addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    var dd = document.getElementById('dd-partido');
    dd.innerHTML = '';
    if (!q) { dd.classList.add('hidden'); return; }
    var filtered = partidoOptions.filter(function(p){ return p.name.toLowerCase().includes(q); }).slice(0, 20);
    if (!filtered.length) { dd.classList.add('hidden'); return; }
    filtered.forEach(function(p) {
        var li = document.createElement('li');
        li.className = 'dd-item';
        li.textContent = p.name;
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById('input-partido').value = p.name;
            partidoId = p.id;
            dd.classList.add('hidden');
            cargarLocalidades(p.id);
        });
        dd.appendChild(li);
    });
    dd.classList.remove('hidden');
});

document.getElementById('input-partido').addEventListener('blur', function() {
    setTimeout(function(){ document.getElementById('dd-partido').classList.add('hidden'); }, 150);
});

function cargarLocalidades(pid) {
    var inpL = document.getElementById('input-localidad');
    inpL.value = ''; inpL.disabled = true;
    document.getElementById('spin-localidad').classList.remove('hidden');
    fetch('/geo/localidades?partido_id=' + pid)
        .then(function(r){ return r.json(); })
        .then(function(data){
            inpL._localidades = data;
            inpL.disabled = false;
            document.getElementById('spin-localidad').classList.add('hidden');
        });
}

document.getElementById('input-localidad').addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    var dd = document.getElementById('dd-localidad');
    dd.innerHTML = '';
    var locs = this._localidades || [];
    if (!q) { dd.classList.add('hidden'); return; }
    var filtered = locs.filter(function(l){ return l.toLowerCase().includes(q); }).slice(0, 20);
    if (!filtered.length) { dd.classList.add('hidden'); return; }
    filtered.forEach(function(l) {
        var li = document.createElement('li');
        li.className = 'dd-item';
        li.textContent = l;
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById('input-localidad').value = l;
            dd.classList.add('hidden');
        });
        dd.appendChild(li);
    });
    dd.classList.remove('hidden');
});

document.getElementById('input-localidad').addEventListener('blur', function() {
    setTimeout(function(){ document.getElementById('dd-localidad').classList.add('hidden'); }, 150);
});

// ===================== INIT CON OLD() VALUES =====================
(function() {
    var prov = document.getElementById('sel-province').value;
    var oldPartido  = window.CREATE_OLD_PARTIDO  || '';
    var oldLocality = window.CREATE_OLD_LOCALITY || '';
    if (!prov) return;
    fetch('/geo/partidos?province_id=' + encodeURIComponent(prov))
        .then(function(r){ return r.json(); })
        .then(function(data){
            partidoOptions = data;
            if (oldPartido) {
                document.getElementById('input-partido').disabled = false;
                var match = data.find(function(p){ return p.name === oldPartido; });
                if (match) {
                    partidoId = match.id;
                    cargarLocalidades(match.id);
                    if (oldLocality) {
                        setTimeout(function(){
                            document.getElementById('input-localidad').value = oldLocality;
                        }, 600);
                    }
                }
            }
        });
})();

// ===================== REGLAS =====================
var reglas = [];

(function() {
    if (window.CREATE_OLD_RULES && window.CREATE_OLD_RULES.length) {
        reglas = window.CREATE_OLD_RULES;
        renderReglas();
    }
})();

document.getElementById('regla-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); addRegla(); }
});

window.addRegla = function addRegla() {
    var val = document.getElementById('regla-input').value.trim();
    if (!val || reglas.includes(val)) return;
    reglas.push(val);
    document.getElementById('regla-input').value = '';
    renderReglas();
}

window.removeRegla = function removeRegla(i) {
    reglas.splice(i, 1);
    renderReglas();
}

function renderReglas() {
    var list = document.getElementById('reglas-list');
    list.innerHTML = '';
    reglas.forEach(function(r, i) {
        var span = document.createElement('span');
        span.className = 'inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-sm font-medium px-3 py-1.5 rounded-full';
        span.innerHTML = '<span>' + r.replace(/</g,'&lt;') + '</span><button type="button" onclick="removeRegla(' + i + ')" class="text-indigo-400 hover:text-indigo-700 leading-none">&times;</button>';
        list.appendChild(span);
    });
    document.getElementById('rules-hidden').value = reglas.join('\n');
}

// ===================== COMODIDADES CUSTOM =====================
document.getElementById('custom-amenity-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); addAmenity(); }
});

window.addAmenity = function addAmenity() {
    var val = document.getElementById('custom-amenity-input').value.trim();
    if (!val) return;
    var list = document.getElementById('custom-amenities-list');
    var span = document.createElement('span');
    span.className = 'flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm px-3 py-1.5 rounded-lg';
    span.innerHTML = '<input type="hidden" name="amenities[]" value="' + val.replace(/"/g,'&quot;') + '"><span>' + val.replace(/</g,'&lt;') + '</span><button type="button" onclick="this.parentElement.remove()" class="text-indigo-400 hover:text-indigo-600 ml-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>';
    list.appendChild(span);
    document.getElementById('custom-amenity-input').value = '';
}

// ===================== FOTOS =====================
var newFiles = [];

document.getElementById('photo-file-input').addEventListener('change', function() {
    addPhotos(Array.from(this.files));
    this.value = '';
});

var dragCounter = 0;
window.addEventListener('dragenter', function(e) {
    if (!e.dataTransfer.types.includes('Files')) return;
    dragCounter++;
    document.getElementById('drag-overlay').classList.add('active');
});
window.addEventListener('dragleave', function() {
    if (--dragCounter <= 0) { dragCounter = 0; document.getElementById('drag-overlay').classList.remove('active'); }
});
window.addEventListener('dragover', function(e){ e.preventDefault(); });
window.addEventListener('drop', function(e) {
    e.preventDefault(); dragCounter = 0;
    document.getElementById('drag-overlay').classList.remove('active');
    var files = Array.from(e.dataTransfer.files).filter(function(f){ return f.type.startsWith('image/'); });
    if (files.length) addPhotos(files);
});

function addPhotos(files) {
    newFiles = newFiles.concat(files);
    renderPhotos();
}

window.removeNew = function removeNew(i) {
    newFiles.splice(i, 1);
    renderPhotos();
}

function renderPhotos() {
    document.querySelectorAll('.pcard.new').forEach(function(el){ el.remove(); });
    var addBtn = document.getElementById('photo-add-btn');
    newFiles.forEach(function(file, i) {
        var url = URL.createObjectURL(file);
        var card = document.createElement('div');
        card.className = 'pcard new';
        card.innerHTML = '<img src="' + url + '" alt="" draggable="false">'
            + '<button type="button" class="pcard-del" onclick="removeNew(' + i + ')">'
            + '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>'
            + (i === 0 ? '<span class="pcard-badge" style="background:rgba(99,102,241,.85)">⭐ Portada</span>' : '<span class="pcard-badge" style="background:rgba(99,102,241,.85)">Nueva</span>');
        addBtn.parentNode.insertBefore(card, addBtn);
    });
}

// ===================== VALIDACION + SUBMIT =====================
function setErr(el, msg) {
    console.log('[setErr] tag:', el.tagName, '| name:', el.getAttribute('name'), '| classes antes:', el.className);
    el.classList.add('inp-err');
    console.log('[setErr] classes despues:', el.className);
    var container = el.parentElement;
    if (container && !container.querySelector('.err-msg')) {
        var sp = document.createElement('span');
        sp.className = 'err-msg';
        sp.textContent = msg;
        container.appendChild(sp);
    }
}

function clearErr(el) {
    el.classList.remove('inp-err');
    // TomSelect: el mensaje está como siguiente hermano del wrapper
    if (el.classList.contains('ts-wrapper')) {
        if (el.nextElementSibling && el.nextElementSibling.classList.contains('err-msg')) {
            el.nextElementSibling.remove();
        }
        return;
    }
    // Inputs/selects normales: el mensaje está en el contenedor padre
    var container = el.parentElement;
    if (container) {
        var sp = container.querySelector('.err-msg');
        if (sp) sp.remove();
    }
}

document.getElementById('create-form').addEventListener('input', function(e) {
    if (e.target.classList.contains('inp-err')) clearErr(e.target);
});
document.getElementById('create-form').addEventListener('change', function(e) {
    if (e.target.classList.contains('inp-err')) clearErr(e.target);
    // limpiar Tom Select wrapper
    var wrap = e.target.closest ? e.target.closest('.ts-wrapper') : null;
    if (wrap) clearErr(wrap);
});

/**
 * Valida los campos obligatorios del formulario.
 * Retorna true si todo es válido; false si hay errores (marca los campos en rojo).
 */
function validateForm() {
    document.querySelectorAll('.inp-err').forEach(function(el) { clearErr(el); });

    var form = document.getElementById('create-form');
    var errores = [];

    function req(name, msg) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) { console.warn('[req] campo no encontrado:', name); return; }
        var v = (el.value || '').trim();
        var invalid = !v || (el.type === 'number' && (isNaN(parseFloat(v)) || parseFloat(v) < 1));
        console.log('[req]', name, '| value:', JSON.stringify(el.value), '| disabled:', el.disabled, '| invalid:', invalid);
        if (invalid) { setErr(el, msg); errores.push(el); }
    }

    // Para campos numéricos donde 0 es válido: solo valida que no esté vacío
    function reqNum(name, msg) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        if (el.value === '' || el.value === null || el.value === undefined) {
            setErr(el, msg); errores.push(el);
        }
    }

    req('type',           'El tipo de propiedad es obligatorio.');
    req('name',           'El nombre es obligatorio.');
    req('description',    'La descripción es obligatoria.');
    // Provincia: validar el select visible (sel-province), no el hidden
    (function() {
        var sel = document.getElementById('sel-province');
        if (!sel || !sel.value) { setErr(sel, 'Seleccioná una provincia.'); errores.push(sel); }
    })();
    req('partido',        'El partido es obligatorio.');
    req('locality',       'La localidad es obligatoria.');
    req('street_name',    'La calle es obligatoria.');
    req('street_number',  'El número es obligatorio.');
    req('capacity',       'La capacidad es obligatoria.');
    reqNum('bedrooms',     'Las habitaciones son obligatorias (podés ingresar 0).');
    reqNum('bathrooms',    'Los baños son obligatorios (podés ingresar 0).');
    reqNum('parking_spots','Los estacionamientos son obligatorios (podés ingresar 0).');
    req('price_per_hour', 'El precio por hora es obligatorio.');
    req('price_per_day',  'El precio por día es obligatorio.');

    console.log('[validateForm] campos con error:', errores.length, errores.map(function(e){ return e.getAttribute ? e.getAttribute('name') || e.className : '?'; }));

    if (errores.length > 0) {
        errores[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return true;
}

document.getElementById('create-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var valid = validateForm();
    console.log('[validateForm] resultado:', valid);
    if (!valid) return;

    if (newFiles.length) {
        var dt = new DataTransfer();
        newFiles.forEach(function(f){ dt.items.add(f); });
        document.getElementById('photos-hidden-input').files = dt.files;
    }

    this.submit();
});
