(function () {
    let newFiles = [];
    const toDelete = new Set(); // IDs of existing photos to delete on save

    // Exposed for submitEdit() — runs inside IIFE scope where newFiles/toDelete are accessible
    window._syncPhotosAndSubmit = function () {
        if (newFiles.length) {
            var dt = new DataTransfer();
            newFiles.forEach(function (f) { dt.items.add(f); });
            document.getElementById('photos-hidden-input').files = dt.files;
        }
        document.querySelectorAll('input[name="delete_images[]"]').forEach(function (el) { el.remove(); });
        toDelete.forEach(function (id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'delete_images[]'; inp.value = id;
            document.getElementById('edit-form').appendChild(inp);
        });
        document.getElementById('edit-form').submit();
    };

    // --- Remove existing photo from UI, queue for deletion on save ---
    window.toggleDelete = function (btn) {
        const card = btn.closest('.pcard');
        const id = card.dataset.id;
        toDelete.add(id);
        card.style.transition = 'opacity .2s, transform .2s';
        card.style.opacity = 0;
        card.style.transform = 'scale(.9)';
        setTimeout(() => card.remove(), 200);
    };

    // --- Drag & drop ---
    let dragCounter = 0;
    window.addEventListener('dragenter', function (e) {
        if (!e.dataTransfer.types.includes('Files')) return;
        dragCounter++;
        document.getElementById('drag-overlay').classList.add('active');
    });
    window.addEventListener('dragleave', function () {
        if (--dragCounter <= 0) {
            dragCounter = 0;
            document.getElementById('drag-overlay').classList.remove('active');
        }
    });
    window.addEventListener('dragover', e => e.preventDefault());
    window.addEventListener('drop', function (e) {
        e.preventDefault();
        dragCounter = 0;
        document.getElementById('drag-overlay').classList.remove('active');
        const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        if (files.length) addPhotos(files);
    });

    // --- File picker ---
    document.getElementById('photo-file-input').addEventListener('change', function () {
        addPhotos(Array.from(this.files));
        this.value = '';
    });

    function addPhotos(files) {
        newFiles = newFiles.concat(files);
        renderNew();
    }

    function renderNew() {
        document.querySelectorAll('.pcard.new').forEach(el => el.remove());
        const addBtn = document.getElementById('photo-add-btn');
        newFiles.forEach((file, i) => {
            const url = URL.createObjectURL(file);
            const card = document.createElement('div');
            card.className = 'pcard new';
            card.innerHTML =
                '<img src="' + url + '" alt="" draggable="false">' +
                '<button type="button" class="pcard-del" onclick="removeNew(' + i + ')" title="Quitar">' +
                  '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
                '<span class="pcard-badge" style="background:rgba(99,102,241,.85)">Nueva</span>';
            addBtn.parentNode.insertBefore(card, addBtn);
        });
    }

    window.removeNew = function (index) {
        newFiles.splice(index, 1);
        renderNew();
    };
})();

// ===================== REGLAS =====================
(function () {
    var reglas = window.EDIT_REGLAS || [];

    document.getElementById('regla-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addRegla(); }
    });

    window.addRegla = function () {
        var val = document.getElementById('regla-input').value.trim();
        if (!val || reglas.includes(val)) return;
        reglas.push(val);
        document.getElementById('regla-input').value = '';
        renderReglas();
    };

    window.removeRegla = function (i) {
        reglas.splice(i, 1);
        renderReglas();
    };

    function renderReglas() {
        var list = document.getElementById('reglas-list');
        list.innerHTML = '';
        reglas.forEach(function (r, i) {
            var span = document.createElement('span');
            span.className = 'inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-sm font-medium px-3 py-1.5 rounded-full';
            span.innerHTML = '<span>' + r.replace(/</g, '&lt;') + '</span><button type="button" onclick="removeRegla(' + i + ')" class="text-indigo-400 hover:text-indigo-700 leading-none">&times;</button>';
            list.appendChild(span);
        });
        document.getElementById('rules-hidden').value = reglas.join('\n');
    }

    renderReglas();

    // ===================== SUBMIT =====================
    window.submitEdit = function () {
        document.querySelectorAll('.input-err').forEach(function (e) { e.classList.remove('input-err'); });
        document.querySelectorAll('.err-txt').forEach(function (e) { e.remove(); });

        var errores = [];

        function chk(el, msg) {
            if (!el) return;
            var v = (el.value || '').trim();
            if (!v || (el.type === 'number' && parseFloat(v) < 1)) {
                el.classList.add('input-err');
                var ex = el.parentElement.querySelector('.err-txt');
                if (!ex) { var sp = document.createElement('span'); sp.className = 'err-txt'; sp.textContent = msg; el.parentElement.appendChild(sp); }
                errores.push(el);
            } else {
                el.classList.remove('input-err');
                var ex = el.parentElement.querySelector('.err-txt'); if (ex) ex.remove();
            }
        }

        function chkNum(el, msg) {
            if (!el) return;
            if (el.value === '' || el.value === null) {
                el.classList.add('input-err');
                var ex = el.parentElement.querySelector('.err-txt');
                if (!ex) { var sp = document.createElement('span'); sp.className = 'err-txt'; sp.textContent = msg; el.parentElement.appendChild(sp); }
                errores.push(el);
            } else {
                el.classList.remove('input-err');
                var ex = el.parentElement.querySelector('.err-txt'); if (ex) ex.remove();
            }
        }

        var form = document.getElementById('edit-form');

        chk(form.querySelector('[name="type"]'),           'El tipo de propiedad es obligatorio.');
        chk(form.querySelector('[name="name"]'),           'El nombre es obligatorio.');
        chk(form.querySelector('[name="description"]'),    'La descripción es obligatoria.');
        chk(form.querySelector('[name="state"]'),          'Seleccioná una provincia.');
        chk(form.querySelector('[name="partido"]'),        'El partido es obligatorio.');
        chk(form.querySelector('[name="locality"]'),       'La localidad es obligatoria.');
        chk(form.querySelector('[name="street_name"]'),    'La calle es obligatoria.');
        chk(form.querySelector('[name="street_number"]'),  'El número es obligatorio.');
        chk(form.querySelector('[name="capacity"]'),       'La capacidad es obligatoria.');
        chkNum(form.querySelector('[name="bedrooms"]'),      'Las habitaciones son obligatorias.');
        chkNum(form.querySelector('[name="bathrooms"]'),     'Los baños son obligatorios.');
        chkNum(form.querySelector('[name="parking_spots"]'), 'Los estacionamientos son obligatorios.');
        chk(form.querySelector('[name="price_per_hour"]'), 'El precio por hora es obligatorio.');

        if (errores.length > 0) {
            var scrollTarget = errores[0].closest('div') || errores[0];
            if (scrollTarget) scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        window._syncPhotosAndSubmit();
    };

    document.getElementById('edit-form').addEventListener('input', function (e) {
        e.target.classList.remove('input-err');
        var ex = e.target.parentElement.querySelector('.err-txt'); if (ex) ex.remove();
    });
    document.getElementById('edit-form').addEventListener('change', function (e) {
        e.target.classList.remove('input-err');
        var wrap = e.target.closest('.ts-wrapper'); if (wrap) wrap.classList.remove('input-err');
        var ex = (wrap || e.target).parentElement.querySelector('.err-txt'); if (ex) ex.remove();
    });
})();
