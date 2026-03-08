import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: '../../public/build-userswallet',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            input: [
                'Resources/assets/sass/app.scss',
                'Resources/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
