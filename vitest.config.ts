import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitest/config';

// Standalone config for component tests — deliberately excludes the Laravel,
// Tailwind, Inertia, and Wayfinder plugins, which expect a real build context.
export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    test: {
        environment: 'happy-dom',
        include: ['resources/js/**/*.spec.ts'],
    },
});
