import { resolve } from 'node:path';
import type { StorybookConfig } from '@storybook/vue3-vite';

const config: StorybookConfig = {
    stories: ['../resources/js/**/*.stories.@(ts|vue)'],
    addons: [
        '@storybook/addon-docs',
        '@storybook/addon-a11y',
        '@storybook/addon-themes',
        'storybook-addon-pseudo-states',
    ],
    framework: {
        name: '@storybook/vue3-vite',
        options: {},
    },
    viteFinal: async (viteConfig) => {
        // Match the app's `@` -> resources/js alias (from tsconfig paths).
        viteConfig.resolve ??= {};
        viteConfig.resolve.alias = {
            ...viteConfig.resolve.alias,
            '@': resolve(process.cwd(), 'resources/js'),
        };

        // Storybook loads the app's vite.config.ts; drop the app-only plugins
        // Storybook doesn't need. laravel-vite-plugin in particular resolves a
        // Herd/Valet dev TLD and crashes `storybook dev` in this environment.
        const appOnly = ['laravel', 'inertia', 'wayfinder'];
        viteConfig.plugins = (viteConfig.plugins ?? [])
            .flat(Infinity)
            .filter(
                (plugin) =>
                    !appOnly.some((name) =>
                        (plugin && 'name' in plugin ? plugin.name : '').includes(
                            name,
                        ),
                    ),
            );

        return viteConfig;
    },
};

export default config;
