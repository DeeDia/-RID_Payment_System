// resources/js/app.jsx — Inertia.js entry point

import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = 'NexusPay';

createInertiaApp({
    title: (title) => `${title} — ${appName}`,

    // Resolve pages from resources/js/Pages/
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx')
        ),

    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },

    // Show a loading indicator while pages load
    progress: {
        color: '#00D4AA',
        delay: 100,
    },
});
