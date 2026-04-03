@props(['value' => '', 'errorId' => null, 'showWhatsapp' => true])

@php $uid = 'iti_' . uniqid(); @endphp

@once('iti-assets')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24/build/js/intlTelInput.min.js"></script>
<style>
/* Wrapper */
.iti { width: 100%; position: relative; }

/* Input */
.iti__tel-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 14px;
    color: #111827;
    background: #fff;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    font-family: inherit;
}
.iti__tel-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.iti__tel-input::placeholder { color: #9ca3af; }

/* Flag / dial code button */
.iti__country-container {
    padding: 0;
}
.iti__selected-country {
    background: #f9fafb;
    border-right: 1px solid #d1d5db;
    border-radius: 11px 0 0 11px;
    padding: 0 10px 0 12px;
    gap: 6px;
    transition: background .15s;
}
.iti__selected-country:hover,
.iti__selected-country[aria-expanded="true"] {
    background: #f3f4f6;
}
.iti__selected-country-arrow {
    color: #6b7280;
    font-size: 10px;
}
.iti__selected-dial-code {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

/* Dropdown — force absolute, not fixed */
.iti__dropdown-content {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: auto !important;
    bottom: auto !important;
    transform: none !important;
    margin-top: 4px !important;
    width: 280px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 8px 24px -4px rgba(0,0,0,.14), 0 2px 8px -2px rgba(0,0,0,.08);
    overflow: hidden;
    z-index: 9999;
}
.iti__search-input {
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    padding: 8px 12px;
    font-size: 13px;
    font-family: inherit;
    color: #374151;
    background: #f9fafb;
    width: 100%;
    outline: none;
    transition: border-color .15s, background .15s;
}
.iti__search-input:focus {
    border-color: #6366f1;
    background: #fff;
}
.iti__country {
    padding: 8px 14px;
    font-size: 13px;
    color: #374151;
    transition: background .1s;
}
.iti__country:hover,
.iti__country.iti__highlight {
    background: #eef2ff;
    color: #4338ca;
}
.iti__country-name { font-size: 13px; }
.iti__dial-code { color: #9ca3af; font-size: 12px; font-weight: 500; }
</style>
@endonce

<div class="space-y-1.5">
    <input id="{{ $uid }}" type="tel" placeholder="11 1234-5678"
           autocomplete="tel">
    <input type="hidden" name="phone" id="{{ $uid }}_h" value="{{ $value }}">

    <p class="text-xs text-gray-400">Seleccioná el código de país e ingresá tu número.</p>

    @if($errorId)
    <p id="{{ $errorId }}" class="text-sm text-red-600 hidden"></p>
    @endif

    <a id="{{ $uid }}_wa" href="#" target="_blank" rel="noopener" style="display:none" @if(!$showWhatsapp) data-no-wa="1" @endif
       class="inline-flex items-center gap-1.5 text-xs text-green-600 hover:text-green-700 font-medium transition-colors">
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
        Abrir en WhatsApp
    </a>
</div>

<script>
(function() {
    var input  = document.getElementById('{{ $uid }}');
    var hidden = document.getElementById('{{ $uid }}_h');
    var waLink = document.getElementById('{{ $uid }}_wa');
    var initVal = @js($value);

    function initIti() {
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
            var num    = iti.getNumber() || '';
            hidden.value = num;
            if (waLink.dataset.noWa) return;
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
    }

    if (typeof window.intlTelInput !== 'undefined') {
        initIti();
    } else {
        document.addEventListener('DOMContentLoaded', initIti);
    }
})();
</script>
