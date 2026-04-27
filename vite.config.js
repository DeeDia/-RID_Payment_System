import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import inertia from '@inertiajs/vite'

export default defineConfig({

    server: {
        host: '127.0.0.1', // force IPv4
        port: 5173,
    },
    
    plugins: [
        laravel({
            // Entry points — CSS is imported inside app.jsx, not listed separately
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        inertia(),
    ],

    resolve: {
        alias: {
            // '@/' maps to resources/js/ — used in imports like @/Components/FormField
            '@': '/resources/js',
        },
    },

//     build: {
//     chunkSizeWarningLimit: 600,
//     rollupOptions: {
//         output: {
//             manualChunks(id) {
//                 if (id.includes('node_modules')) {

//                     if (id.includes('react')) {
//                         return 'react';
//                     }

//                     if (id.includes('@inertiajs')) {
//                         return 'inertia';
//                     }

//                     return 'vendor';
//                 }
//             },
//         },
//     },
// },
});
