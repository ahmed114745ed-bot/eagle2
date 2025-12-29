import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
    build: {
        outDir: '../../public/build',
        emptyOutDir: false,
        manifest: true,
    },
    plugins: [
        vue(),
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build',
            // Use paths relative to module root so Vite dev server serves them
            input: [
                'Resources/css/app.css',
                'Resources/js/app.js',
            ],
            refresh: [
                'Modules/DynamicTheme/Resources/views/**/*',
                'Modules/DynamicTheme/Resources/js/**/*',
                'Modules/DynamicTheme/Resources/css/**/*',
            ],
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'Resources/js'),
        },
    },
});
