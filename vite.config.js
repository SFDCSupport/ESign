import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            hotFile: "../../../public/vendor/esign/esign.hot",
            buildDirectory: "../../../../public/vendor/esign",
            input: [
                "resources/sass/app.scss",
                "resources/js/app.js",
                "resources/sass/signing.scss",
                "resources/js/signing.js"
            ],
            refresh: [{
                paths: [
                    "resources/js/**",
                    "resources/scss/**",
                    "resources/lang/**",
                    "resources/views/**"
                ],
                config: {
                    delay: 300
                }
            }]
        })
    ],
    resolve: {
        alias: {
            "$": "jQuery",
            "$fonts": "./resources/fonts"
        }
    },
    build: {
        target: "es2022"
    },
    esbuild: {
        target: "es2022"
    },
    optimizeDeps: {
        esbuildOptions: {
            target: "es2022"
        }
    }
});
