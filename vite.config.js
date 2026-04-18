import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Page-specific CSS
                'resources/css/pages/propiedades-create.css',
                'resources/css/pages/propiedades-edit.css',
                'resources/css/pages/propiedades-show.css',
                'resources/css/pages/profile-edit.css',
                'resources/css/pages/messages-conversation.css',
                // Page-specific JS
                'resources/js/pages/propiedades-create.js',
                'resources/js/pages/propiedades-edit.js',
                'resources/js/pages/propiedades-show.js',
                'resources/js/pages/propiedades-index.js',
                'resources/js/pages/profile-edit.js',
                'resources/js/pages/messages-conversation.js',
                'resources/js/pages/owner-propiedades.js',
                'resources/js/pages/owner-reservation-create.js',
                'resources/js/pages/owner-reservation-show.js',
                'resources/js/pages/reservations-process-pending.js',
                'resources/js/pages/home.js',
            ],
            refresh: true,
        }),
    ],
});
