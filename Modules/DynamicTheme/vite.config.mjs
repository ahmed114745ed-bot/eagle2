import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
    build: {
        outDir: path.resolve(__dirname, '../../public/build-dynamic-theme'),
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        vue(),
        laravel({
            publicDirectory: path.resolve(__dirname, '../../public'),
            buildDirectory: 'build-dynamic-theme',
            hotFile: path.resolve(__dirname, '../../storage/framework/vite.dynamic-theme.hot'),
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
    server: {
        host: true,
        strictPort: true,
        port: 5173,
    },
});
