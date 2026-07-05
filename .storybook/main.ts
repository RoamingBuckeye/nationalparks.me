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
    // Match the app's `@` -> resources/js alias (from tsconfig paths).
    viteFinal: async (viteConfig) => {
        viteConfig.resolve ??= {};
        viteConfig.resolve.alias = {
            ...viteConfig.resolve.alias,
            '@': resolve(process.cwd(), 'resources/js'),
        };
        return viteConfig;
    },
};

export default config;
