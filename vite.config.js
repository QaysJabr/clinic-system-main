import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '127.0.0.1',
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/chart.js')) return 'chart';
                    if (id.includes('@fullcalendar')) return 'calendar';
                    if (id.includes('node_modules')) return 'vendor';
                },
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-plans.js',
                'resources/js/platform.js',
                'resources/js/saas-billing.js',
                'resources/js/clinic-dashboard.js',
                'resources/js/appointments-calendar.js',
                'resources/js/appointment-slots.js',
                'resources/js/public-booking.js',
                'resources/css/landing.css',
                'resources/js/landing.js',
            ],
            refresh: true,
        }),
    ],
});
