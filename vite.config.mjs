import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'Modules/DynamicTheme/Resources/css/app.css',
                'Modules/DynamicTheme/Resources/js/app.js',
                'Modules/DynamicTheme/Resources/js/admin.js',
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
            '@dynamictheme': path.resolve(__dirname, 'Modules/DynamicTheme/Resources/js'),
        },
    },
});
