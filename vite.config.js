import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: '../../../public/vendor/esign/esign.hot',
            buildDirectory: '../../../../public/vendor/esign',
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '$': 'jQuery',
            '$fonts': './resources/fonts',
        },
    },
    build: {
        target: "es2022"
    },
    esbuild: {
        target: "es2022"
    },
    optimizeDeps:{
        esbuildOptions: {
            target: "es2022",
        }
    }
});
