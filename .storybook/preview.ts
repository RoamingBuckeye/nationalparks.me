import { withThemeByClassName } from '@storybook/addon-themes';
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
        // Breakpoints matching the CSS (sm 640 / md 768 / lg 1024).
        viewport: {
            options: {
                mobile: {
                    name: 'Mobile · 375',
                    styles: { width: '375px', height: '720px' },
                },
                sm: {
                    name: 'sm · 640',
                    styles: { width: '640px', height: '820px' },
                },
                md: {
                    name: 'md · 768',
                    styles: { width: '768px', height: '900px' },
                },
                lg: {
                    name: 'lg · 1024',
                    styles: { width: '1024px', height: '900px' },
                },
            },
        },
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
    decorators: [
        // First-party light / passport-night toggle — sets .dark on <html>.
        withThemeByClassName({
            themes: { light: '', dark: 'dark' },
            defaultTheme: 'light',
            parentSelector: 'html',
        }),
        (story) => ({
            components: { story },
            template:
                '<div style="padding: 2rem; background: var(--color-background); color: var(--color-foreground);"><story /></div>',
        }),
    ],
};

export default preview;
