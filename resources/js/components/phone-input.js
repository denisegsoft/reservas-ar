/**
 * Initialises intl-tel-input for every <input data-phone-uid="..."> element.
 * The blade component passes uid and initial value via data attributes so this
 * file has no PHP dependency and can be bundled once via app.js.
 */
function initPhoneInputs() {
    document.querySelectorAll('input[data-phone-uid]').forEach(function (input) {
        var uid     = input.dataset.phoneUid;
        var initVal = input.dataset.phoneVal || '';
        var hidden  = document.getElementById(uid + '_h');
        var waLink  = document.getElementById(uid + '_wa');

        var iti = window.intlTelInput(input, {
            initialCountry: 'ar',
            separateDialCode: true,
            autoPlaceholder: 'aggressive',
            placeholderNumberType: 'MOBILE',
            preferredCountries: ['ar', 'uy', 'cl', 'br', 'co', 'mx', 'py', 'bo', 'pe', 'es'],
            utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@24/build/js/utils.js',
            i18n: { searchPlaceholder: 'Buscar país...' },
        });

        if (initVal) iti.setNumber(initVal);

        function sync() {
            var num = iti.getNumber() || '';
            if (hidden) hidden.value = num;
            if (!waLink || waLink.dataset.noWa) return;
            var digits = num.replace(/\D/g, '');
            if (digits.length > 5) {
                waLink.href = 'https://wa.me/' + digits;
                waLink.style.display = 'inline-flex';
            } else {
                waLink.style.display = 'none';
            }
        }

        input.addEventListener('input', sync);
        input.addEventListener('countrychange', sync);
        sync();

        var form = input.closest('form');
        if (form) form.addEventListener('submit', sync, true);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPhoneInputs);
} else {
    initPhoneInputs();
}
