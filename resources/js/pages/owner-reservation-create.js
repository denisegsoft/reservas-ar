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
    calendarModal.open(propName, (window.RC_DISP_RESERVAS || {})[propId] ?? []);
};
