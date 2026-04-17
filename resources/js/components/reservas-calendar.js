/**
 * Calendar modal reutilizable para disponibilidad de reservas.
 * Requiere Bootstrap modal con id="calendarModal".
 * Uso:
 *   calendarModal.open(nombre, reservas)  — abre con datos
 *   calendarModal.open(nombre, reservas, reservaActualId) — excluye una reserva
 */
const calendarModal = (() => {
    const MESES  = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const SEMANA = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

    let _data = [], _blocked = new Set(), _year, _month, _modal;

    function open(nombre, reservas, blockedDates) {
        _data    = reservas;
        _blocked = new Set(blockedDates || []);
        document.getElementById('cal-nombre').textContent = nombre;
        document.getElementById('cal-detail').innerHTML = '';
        const now = new Date();
        _year  = now.getFullYear();
        _month = now.getMonth();
        render();
        if (!_modal) _modal = new bootstrap.Modal(document.getElementById('calendarModal'));
        _modal.show();
    }

    function prev() { if (--_month < 0) { _month = 11; _year--; } render(); }
    function next() { if (++_month > 11) { _month = 0; _year++; } render(); }

    function render() {
        const firstDay  = new Date(_year, _month, 1).getDay();
        const totalDays = new Date(_year, _month + 1, 0).getDate();
        const today     = new Date().toISOString().split('T')[0];

        const dayMap = {};
        _data.forEach(r => {
            let d = new Date(r.check_in + 'T00:00:00');
            const end = new Date(r.check_out + 'T00:00:00');
            while (d <= end) {
                const key = d.toISOString().split('T')[0];
                if (!dayMap[key]) dayMap[key] = [];
                dayMap[key].push(r);
                d.setDate(d.getDate() + 1);
            }
        });

        let html = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
            <button type="button" onclick="calendarModal.prev()" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:5px 12px;cursor:pointer;font-size:1.1rem;color:#374151">&#8249;</button>
            <span style="font-weight:700;font-size:1rem;color:#111827">${MESES[_month]} ${_year}</span>
            <button type="button" onclick="calendarModal.next()" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:5px 12px;cursor:pointer;font-size:1.1rem;color:#374151">&#8250;</button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:4px">
            ${SEMANA.map(d => `<div style="text-align:center;font-size:.68rem;font-weight:600;color:#9ca3af;padding:4px 0">${d}</div>`).join('')}
        </div>
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px">`;

        for (let i = 0; i < firstDay; i++) html += `<div></div>`;

        for (let d = 1; d <= totalDays; d++) {
            const dateStr = `${_year}-${String(_month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const res     = dayMap[dateStr] || [];
            const isToday = dateStr === today;

            const isBlocked = _blocked.has(dateStr);
            let bg = '#f9fafb', color = '#6b7280', border = '1px solid transparent', cursor = 'default', extra = '';
            if (isBlocked && !res.length) {
                bg = '#f3f4f6'; color = '#9ca3af'; border = '1px solid #e5e7eb';
                extra = 'text-decoration:line-through;';
            } else if (res.length) {
                const hasPending   = res.some(r => r.status === 'pending');
                const hasConfirmed = res.some(r => r.status === 'confirmed');
                if (hasPending && hasConfirmed)  { bg = '#fef3c7'; color = '#92400e'; border = '1px solid #fcd34d'; }
                else if (hasPending)             { bg = '#fef9c3'; color = '#854d0e'; border = '1px solid #fde047'; }
                else                             { bg = '#dcfce7'; color = '#166534'; border = '1px solid #86efac'; }
                cursor = 'pointer';
            }
            const todayStyle = isToday ? 'outline:2px solid #6366f1;outline-offset:1px;font-weight:700;' : '';

            html += `<div onclick="${res.length ? `calendarModal.detail('${dateStr}')` : ''}"
                style="text-align:center;padding:7px 2px;border-radius:8px;font-size:.8rem;background:${bg};color:${color};border:${border};cursor:${cursor};${extra}${todayStyle}">${d}</div>`;
        }

        html += `</div>
        <div style="display:flex;gap:16px;margin-top:1rem;justify-content:center;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:6px">
                <div style="width:13px;height:13px;border-radius:4px;background:#fef9c3;border:1px solid #fde047"></div>
                <span style="font-size:.72rem;color:#6b7280">Pendiente</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <div style="width:13px;height:13px;border-radius:4px;background:#dcfce7;border:1px solid #86efac"></div>
                <span style="font-size:.72rem;color:#6b7280">Confirmada</span>
            </div>
            ${_blocked.size ? `<div style="display:flex;align-items:center;gap:6px">
                <div style="width:13px;height:13px;border-radius:4px;background:#f3f4f6;border:1px solid #e5e7eb"></div>
                <span style="font-size:.72rem;color:#6b7280">No disponible</span>
            </div>` : ''}
        </div>`;

        if (!_data.length) {
            html += `<div style="text-align:center;padding:1.5rem 0;color:#9ca3af;font-size:.85rem">No hay reservas pendientes ni confirmadas.</div>`;
        }

        document.getElementById('cal-calendar').innerHTML = html;
    }

    function detail(dateStr) {
        const d   = new Date(dateStr + 'T00:00:00');
        const res = _data.filter(r => d >= new Date(r.check_in + 'T00:00:00') && d <= new Date(r.check_out + 'T00:00:00'));
        if (!res.length) return;

        let html = `<div style="margin-top:1.25rem;border-top:1px solid #f3f4f6;padding-top:1rem">
            <p style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem">Reservas en esta fecha</p>`;

        res.forEach(r => {
            const isPending = r.status === 'pending';
            const bg    = isPending ? '#fef9c3' : '#dcfce7';
            const color = isPending ? '#854d0e' : '#166534';
            const label = isPending ? 'Pendiente' : 'Confirmada';
            html += `
            <div style="background:#f9fafb;border-radius:12px;padding:12px 14px;margin-bottom:8px;border:1px solid #f3f4f6">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <span style="font-weight:700;font-size:.875rem;color:#111827">👤 ${r.guest}</span>
                    <span style="font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:20px;background:${bg};color:${color}">${label}</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.75rem;color:#6b7280">
                    <span>📅 ${r.check_in} → ${r.check_out}</span>
                    <span>👥 ${r.guests} personas</span>
                    <span>💰 $${r.total}</span>
                </div>
                ${r.id ? `<a href="/usuario/reservas/${r.id}" style="display:inline-flex;align-items:center;gap:5px;margin-top:8px;font-size:.75rem;font-weight:600;color:#4f46e5;text-decoration:none">Gestionar reserva →</a>` : ''}
            </div>`;
        });

        html += `</div>`;
        document.getElementById('cal-detail').innerHTML = html;
    }

    return { open, prev, next, detail };
})();

window.calendarModal = calendarModal;
