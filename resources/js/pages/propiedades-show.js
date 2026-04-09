document.addEventListener('DOMContentLoaded', function () {
    const unavailableDates = window.SHOW_UNAVAILABLE_DATES || [];

    const inEl  = document.getElementById('fp_check_in');
    const outEl = document.getElementById('fp_check_out');

    function triggerAlpine(el, value) {
        el.value = value;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const fpOut = flatpickr(outEl, {
        locale: 'es',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        minDate: new Date(Date.now() + 86400000),
        disable: unavailableDates,
        onChange([date]) {
            if (!date) return;
            triggerAlpine(outEl, flatpickr.formatDate(date, 'Y-m-d'));
        },
    });
    window._fpCheckOut = fpOut;

    window._fpCheckIn = flatpickr(inEl, {
        locale: 'es',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        minDate: 'today',
        disable: unavailableDates,
        onChange([date]) {
            if (!date) return;
            const val = flatpickr.formatDate(date, 'Y-m-d');
            triggerAlpine(inEl, val);
            const nextDay = new Date(date.getTime() + 86400000);
            fpOut.set('minDate', nextDay);
            if (outEl._flatpickr.selectedDates[0] <= date) {
                fpOut.clear();
                triggerAlpine(outEl, '');
            }
        },
    });
});

function bookingForm() {
    return {
        checkIn: '',
        checkInTime: '14:00',
        checkOut: '',
        checkOutTime: '11:00',
        totalHours: 0,
        totalDays: 0,
        pricePerHour:    window.SHOW_PRICE_PER_HOUR    || 0,
        pricePerDay:     window.SHOW_PRICE_PER_DAY     || 0,
        dayDiscounts:    window.SHOW_DAY_DISCOUNTS     || [],
        dateDiscounts:   window.SHOW_DATE_DISCOUNTS    || [],
        weekdayDiscounts:window.SHOW_WEEKDAY_DISCOUNTS || [],
        baseTotal: 0,
        total: 0,
        breakdown: [],
        errors: { checkIn: '', checkOut: '', guests: '' },
        isLoggedIn: window.SHOW_IS_LOGGED_IN || false,
        pendingData: window.SHOW_PENDING_RESERVATION || null,

        init() {
            if (this.pendingData) {
                const pd   = this.pendingData;
                const self = this;
                self.checkIn      = pd.check_in      || '';
                self.checkInTime  = pd.check_in_time  || '14:00';
                self.checkOut     = pd.check_out      || '';
                self.checkOutTime = pd.check_out_time || '11:00';
                // Wait for Flatpickr to be initialized (DOMContentLoaded)
                const tryFill = function() {
                    if (window._fpCheckIn && window._fpCheckOut) {
                        if (self.checkIn)  window._fpCheckIn.setDate(self.checkIn,  true);
                        if (self.checkOut) window._fpCheckOut.setDate(self.checkOut, true);
                        self.calculateTotal();
                        if (self.isLoggedIn) {
                            setTimeout(function() { self.$el.submit(); }, 100);
                        }
                    } else {
                        setTimeout(tryFill, 50);
                    }
                };
                setTimeout(tryFill, 50);
            }
        },

        fmt(n) {
            return Number(n).toLocaleString('es-AR', { maximumFractionDigits: 0 });
        },

        calculateTotal() {
            this.breakdown = [];
            if (!this.checkIn || !this.checkOut) return;

            const d1 = new Date(this.checkIn  + 'T' + (this.checkInTime  || '14:00') + ':00');
            const d2 = new Date(this.checkOut + 'T' + (this.checkOutTime || '11:00') + ':00');
            const diffMs = d2 - d1;
            if (diffMs <= 0) { this.totalHours = 0; this.totalDays = 0; this.total = 0; return; }

            this.totalHours = Math.round(diffMs / (1000 * 60 * 60));

            // días = diferencia de fechas calendario (sin horas)
            const d1Day = new Date(this.checkIn  + 'T00:00:00');
            const d2Day = new Date(this.checkOut + 'T00:00:00');
            const totalNights = Math.round((d2Day - d1Day) / (1000 * 60 * 60 * 24));
            this.totalDays = totalNights;

            const sameDay = this.checkIn === this.checkOut;

            if (sameDay) {
                // Mismo día calendario → por hora
                this.baseTotal = this.totalHours * this.pricePerHour;
                this.total     = this.baseTotal;
                this.breakdown = [{ type: 'base', label: `${this.totalHours} hs × $${this.fmt(this.pricePerHour)}/h`, amount: this.total }];
                return;
            }

            // Por días con descuentos por día
            let subtotal  = 0;
            const discountGroups = {}; // key → { discPct, reason, count, saved }

            let cursor = new Date(d1Day);
            while (cursor < d2Day) {
                const dateStr = cursor.toISOString().split('T')[0];
                const weekday = cursor.getDay(); // 0=Dom

                let dayDiscount = 0;
                let discountReason = null;

                // Descuento por día de semana
                for (const t of (this.weekdayDiscounts || [])) {
                    const days = (t.days || []).map(Number);
                    if (days.includes(weekday) && Number(t.discount) > dayDiscount) {
                        dayDiscount    = Number(t.discount);
                        discountReason = 'Día especial';
                    }
                }
                // Descuento por fecha especial
                for (const t of (this.dateDiscounts || [])) {
                    if (!t.date_from || !t.date_to) continue;
                    if (dateStr >= t.date_from && dateStr <= t.date_to && Number(t.discount) > dayDiscount) {
                        dayDiscount    = Number(t.discount);
                        discountReason = 'Fecha especial';
                    }
                }

                const dayPrice = dayDiscount > 0
                    ? Math.round(this.pricePerDay * (1 - dayDiscount / 100))
                    : this.pricePerDay;
                subtotal += dayPrice;

                if (dayDiscount > 0) {
                    const key = dayDiscount + '|' + discountReason;
                    if (!discountGroups[key]) discountGroups[key] = { discPct: dayDiscount, reason: discountReason, count: 0, saved: 0 };
                    discountGroups[key].count++;
                    discountGroups[key].saved += (this.pricePerDay - dayPrice);
                }

                cursor.setDate(cursor.getDate() + 1);
            }

            // Descuento por cantidad de días
            let durationPct = 0;
            const sorted = [...(this.dayDiscounts || [])].sort((a, b) => Number(b.days) - Number(a.days));
            for (const t of sorted) {
                if (totalNights >= Number(t.days)) { durationPct = Number(t.discount); break; }
            }
            const subtotalAfterDuration = durationPct > 0 ? Math.round(subtotal * (1 - durationPct / 100)) : subtotal;
            const savedDuration = subtotal - subtotalAfterDuration;

            this.baseTotal = totalNights * this.pricePerDay;
            this.total     = subtotalAfterDuration;

            // Armar líneas del detalle
            this.breakdown.push({ type: 'base', label: `${totalNights} día${totalNights !== 1 ? 's' : ''} × $${this.fmt(this.pricePerDay)}/día`, amount: this.baseTotal });
            for (const key of Object.keys(discountGroups)) {
                const g = discountGroups[key];
                this.breakdown.push({ type: 'discount', label: `${g.count} día${g.count !== 1 ? 's' : ''} · -${g.discPct}% ${g.reason}`, amount: -g.saved });
            }
            if (durationPct > 0) {
                this.breakdown.push({ type: 'discount', label: `${totalNights} días · -${durationPct}% por estadía`, amount: -savedDuration });
            }
        },

        submitForm() {
            const guestsVal = this.$el.querySelector('[name="guests"]').value;
            this.errors.checkIn  = this.checkIn  ? '' : 'Seleccioná la fecha de entrada';
            this.errors.checkOut = this.checkOut ? '' : 'Seleccioná la fecha de salida';
            this.errors.guests   = guestsVal     ? '' : 'Ingresá la cantidad de personas';
            if (this.errors.checkIn || this.errors.checkOut || this.errors.guests) return;
            this.$el.submit();
        }
    }
}

(function() {
    var col          = document.getElementById('booking-col');
    var stickyDiv    = col.querySelector('.sticky');
    var cardDiv      = stickyDiv.firstElementChild;
    var priceGrid    = document.getElementById('booking-price-grid');
    var mobilePrices = document.getElementById('mobile-prices');
    var fab          = document.getElementById('fab-reservar');
    var sheet        = document.getElementById('booking-sheet');
    var sheetInner   = document.getElementById('sheet-inner');
    var formSlot     = document.getElementById('sheet-form-slot');
    var inMobile     = false;

    function applyMobile() {
        if (inMobile) return;
        inMobile = true;
        col.style.display = 'none';
        mobilePrices.style.display = 'block';
        fab.style.display = 'block';
        if (priceGrid) priceGrid.style.display = 'none';
        formSlot.appendChild(cardDiv);
    }

    function applyDesktop() {
        if (!inMobile) return;
        inMobile = false;
        sheet.style.display = 'none';
        sheetInner.style.transform = 'translateY(100%)';
        stickyDiv.appendChild(cardDiv);
        col.style.display = '';
        mobilePrices.style.display = 'none';
        fab.style.display = 'none';
        if (priceGrid) priceGrid.style.display = '';
    }

    function check() {
        window.innerWidth < 1024 ? applyMobile() : applyDesktop();
    }

    check();

    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(check, 80);
    });

    window.openBookingSheet = function() {
        fab.style.display = 'none';
        sheet.style.display = 'block';
        requestAnimationFrame(function() {
            sheetInner.style.transform = 'translateY(0)';
        });
    };

    window.closeBookingSheet = function() {
        sheetInner.style.transform = 'translateY(100%)';
        setTimeout(function() {
            sheet.style.display = 'none';
            if (inMobile) fab.style.display = 'block';
        }, 350);
    };
})();
