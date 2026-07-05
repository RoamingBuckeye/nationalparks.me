import type { Preview } from '@storybook/vue3-vite';

// The design system: reset + all BEM component layers + tokens.
import '../resources/css/app.css';

const preview: Preview = {
    parameters: {
        controls: {
            matchers: { color: /(background|color)$/i, date: /Date$/i },
        },
        // The app defines its own body background/foreground; don't fight it.
        backgrounds: { disable: true },
        options: {
            storySort: {
                order: [
                    'Design System',
                    ['Colors', 'Typography'],
                    'Atoms',
                    'Molecules',
                ],
            },
        },
    },
    globalTypes: {
        theme: {
            description: 'Passport theme',
            defaultValue: 'light',
            toolbar: {
                title: 'Theme',
                icon: 'paintbrush',
                items: [
                    { value: 'light', title: 'Light' },
                    { value: 'dark', title: 'Passport night' },
                ],
                dynamicTitle: true,
            },
        },
    },
    decorators: [
        (story, context) => {
            document.documentElement.classList.toggle(
                'dark',
                context.globals.theme === 'dark',
            );

            return {
                components: { story },
                template:
                    '<div style="padding: 2rem; background: var(--color-background); color: var(--color-foreground);"><story /></div>',
            };
        },
    ],
};

export default preview;
