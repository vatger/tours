import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
<<<<<<< HEAD
import i18n from 'laravel-vue-i18n/vite';
import path from 'path';
=======
import tailwindcss from '@tailwindcss/vite';
>>>>>>> fbae2a4df2eec7e4f7c857ffe50db2b78473ce3f
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        i18n(),
    ],
});
