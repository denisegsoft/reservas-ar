document.addEventListener('alpine:init', () => {
    Alpine.data('chat', () => ({
        body: '',
        sending: false,
        hasReservation: window.CHAT_HAS_RESERVATION || false,
        reservationId:  window.CHAT_RESERVATION_ID  || null,
        storeUrl:       window.CHAT_STORE_URL        || '',
        csrfToken:      window.CHAT_CSRF_TOKEN       || '',
        messages:       window.CHAT_MESSAGES         || [],

        init() {
            this.$nextTick(() => {
                this.$refs.container.scrollTop = this.$refs.container.scrollHeight;
            });
        },

        async send() {
            if (!this.body.trim() || this.sending) return;
            this.sending = true;
            const text = this.body;
            this.body = '';
            try {
                const res = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ body: text, reservation_id: this.reservationId }),
                });
                if (!res.ok) { this.body = text; return; }
                const msg = await res.json();
                this.messages.push({ ...msg, mine: true, read_at: null });
                this.$nextTick(() => {
                    this.$refs.container.scrollTop = this.$refs.container.scrollHeight;
                });
            } finally {
                this.sending = false;
            }
        },
    }));
});
