document.addEventListener('alpine:init', () => {
    Alpine.data('reservaForm', () => ({
        existingClient: true,
        selectedProperty: null,
        properties: window.RC_PROPERTIES || [],
        calcTotal() {},
    }));
});

window.abrirCalendarioDisponibilidad = function () {
    const propId   = document.querySelector('[name="property_id"]')?.value;
    const propName = document.querySelector('[name="property_id"] option:checked')?.text ?? 'Propiedad';
    if (!propId) { alert('Primero seleccioná una propiedad.'); return; }
    const reservas = (window.RC_DISP_RESERVAS || {})[propId] ?? [];
    const blocked  = (window.RC_BLOCKED_DATES || {})[propId] ?? [];
    calendarModal.open(propName, reservas, blocked);
};
