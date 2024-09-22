import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        lib: {
            entry: './dist/index.js',
            name: 'FormRenderer',
            fileName: 'fr_bundle',
            formats: ['iife'],
        },
        outDir: '../../public/js/',
        rollupOptions: {
            external: [],
            output: {
                globals: {
                },
            },
        },
    },
});
