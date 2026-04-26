import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            // Entry points — CSS is imported inside app.jsx, not listed separately
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        react(),
    ],

    resolve: {
        alias: {
            // '@/' maps to resources/js/ — used in imports like @/Components/FormField
            '@': '/resources/js',
        },
    },

    build: {
        // Raise warning threshold (banking portal has more code than a simple site)
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                // Split vendor code for better caching
                manualChunks: {
                    react:   ['react', 'react-dom'],
                    inertia: ['@inertiajs/react'],
                },
            },
        },
    },
});
