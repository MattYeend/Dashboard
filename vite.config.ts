import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';
import { visualizer } from 'rollup-plugin-visualizer';

const analyse = process.env.ANALYSE === 'true';

// The Docker node container has no PHP, so Wayfinder cannot run there.
// Generate the route files from the app container instead.
const inDocker = process.env.DOCKER === 'true';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: process.env.CI ? [] : [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        !inDocker && wayfinder({
            formVariants: true,
        }),
        analyse && visualizer({
            filename: './build-stats/stats.json',
            template: 'raw-data',
            gzipSize: true,
            brotliSize: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'dashboard.test',
        },
    },
    build: {
        rollupOptions: {
            onwarn(warning, warn) {
                if (warning.code === 'INVALID_ANNOTATION') return;
                warn(warning);
            },
        },
    },
});