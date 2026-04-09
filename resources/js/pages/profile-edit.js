// ── Inline alert ──────────────────────────────────────────────
function showAlert(alertId, msg, type) {
    const el = document.getElementById(alertId);
    const isSuccess = type === 'success';
    el.className = 'alert-in flex items-center gap-3 rounded-xl px-4 py-3 text-sm border ' +
        (isSuccess
            ? 'bg-green-50 text-green-800 border-green-200'
            : 'bg-red-50 text-red-800 border-red-200');
    el.innerHTML = `
        <span class="shrink-0 text-base">${isSuccess ? '✓' : '✕'}</span>
        <span class="flex-1">${msg}</span>
        <button type="button" onclick="closeAlert('${alertId}')" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity leading-none text-lg">&times;</button>
    `;
    el.classList.remove('hidden');
}

function closeAlert(alertId) {
    const el = document.getElementById(alertId);
    el.classList.add('hidden');
}

// ── Field errors ──────────────────────────────────────────────
function showFieldErrors(form, errors) {
    form.querySelectorAll('[id^="err_"]').forEach(el => {
        el.textContent = '';
        el.classList.add('hidden');
        const field = form.querySelector(`[name="${el.id.slice(4)}"]`);
        if (field) field.classList.remove('border-red-400');
    });
    Object.entries(errors).forEach(([field, msgs]) => {
        const el    = document.getElementById('err_' + field);
        const input = form.querySelector(`[name="${field}"]`);
        if (el) { el.textContent = msgs[0]; el.classList.remove('hidden'); }
        if (input) input.classList.add('border-red-400');
    });
}

function clearFieldErrors(form) {
    form.querySelectorAll('[id^="err_"]').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
    form.querySelectorAll('input').forEach(el => el.classList.remove('border-red-400'));
}

// ── Spinner ───────────────────────────────────────────────────
function setBusy(btnId, spinnerId, busy) {
    const btn = document.getElementById(btnId);
    const sp  = document.getElementById(spinnerId);
    btn.disabled = busy;
    sp.classList.toggle('hidden', !busy);
}

// ── Profile info form ─────────────────────────────────────────
document.getElementById('profileInfoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    setBusy('profileInfoBtn', 'profileInfoSpinner', true);
    clearFieldErrors(this);
    closeAlert('profileInfoAlert');

    const fd = new FormData(this);
    fd.append('_method', 'PATCH');

    try {
        const res  = await fetch(window.PROFILE_ROUTE_UPDATE, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') },
            body: fd,
        });
        const data = await res.json();

        if (res.ok) {
            showAlert('profileInfoAlert', data.message, 'success');
            if (data.avatar_url) {
                document.querySelectorAll('img[data-avatar]').forEach(img => img.src = data.avatar_url);
            }
        } else if (res.status === 422) {
            showFieldErrors(this, data.errors ?? {});
            showAlert('profileInfoAlert', 'Revisá los errores del formulario.', 'error');
        } else {
            showAlert('profileInfoAlert', 'Ocurrió un error. Intentá de nuevo.', 'error');
        }
    } catch {
        showAlert('profileInfoAlert', 'Error de conexión. Intentá de nuevo.', 'error');
    } finally {
        setBusy('profileInfoBtn', 'profileInfoSpinner', false);
    }
});

// ── Password form ─────────────────────────────────────────────
const pwdMessages = {
    'validation.current_password': 'La contraseña actual es incorrecta.',
    'validation.min.string':       'La nueva contraseña debe tener al menos 8 caracteres.',
    'validation.confirmed':        'Las contraseñas no coinciden.',
    'validation.required':         'Este campo es obligatorio.',
};

function translateErrors(errors) {
    const out = {};
    Object.entries(errors).forEach(([field, msgs]) => {
        out[field] = msgs.map(m => pwdMessages[m] ?? m);
    });
    return out;
}

document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    setBusy('passwordBtn', 'passwordSpinner', true);
    clearFieldErrors(this);
    closeAlert('passwordAlert');

    const fd = new FormData(this);
    fd.append('_method', 'PUT');

    try {
        const res  = await fetch(window.PROFILE_ROUTE_PASSWORD_UPDATE, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') },
            body: fd,
        });
        const data = await res.json();

        if (res.ok) {
            showAlert('passwordAlert', data.message, 'success');
            this.reset();
        } else if (res.status === 422) {
            showFieldErrors(this, translateErrors(data.errors ?? {}));
            showAlert('passwordAlert', 'Revisá los errores.', 'error');
        } else {
            showAlert('passwordAlert', 'Ocurrió un error. Intentá de nuevo.', 'error');
        }
    } catch {
        showAlert('passwordAlert', 'Error de conexión. Intentá de nuevo.', 'error');
    } finally {
        setBusy('passwordBtn', 'passwordSpinner', false);
    }
});

// ── Delete account form ───────────────────────────────────────
document.getElementById('deleteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    setBusy('deleteBtn', 'deleteSpinner', true);
    document.getElementById('err_del_password').classList.add('hidden');
    closeAlert('deleteAlert');

    const fd = new FormData(this);

    try {
        const res  = await fetch(window.PROFILE_ROUTE_DESTROY, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') },
            body: new URLSearchParams({ _method: 'DELETE', _token: fd.get('_token'), password: fd.get('password') }),
        });
        const data = await res.json();

        if (res.ok) {
            window.location.href = data.redirect ?? '/';
        } else if (res.status === 422) {
            const msg = data.errors?.password?.[0] ?? 'Contraseña incorrecta.';
            const err = document.getElementById('err_del_password');
            err.textContent = msg;
            err.classList.remove('hidden');
            document.getElementById('del_password').classList.add('border-red-400');
        } else {
            showAlert('deleteAlert', 'Ocurrió un error. Intentá de nuevo.', 'error');
        }
    } catch {
        showAlert('deleteAlert', 'Error de conexión. Intentá de nuevo.', 'error');
    } finally {
        setBusy('deleteBtn', 'deleteSpinner', false);
    }
});
