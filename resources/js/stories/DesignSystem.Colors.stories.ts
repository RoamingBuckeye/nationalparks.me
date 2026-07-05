import type { Meta, StoryObj } from '@storybook/vue3-vite';

const semantic = [
    'background',
    'foreground',
    'card',
    'popover',
    'primary',
    'secondary',
    'muted',
    'muted-foreground',
    'accent',
    'destructive',
    'border',
    'input',
    'ring',
];

const brand = ['brand-300', 'brand-400', 'brand-700', 'brand-800'];

const meta = {
    title: 'Design System/Colors',
    parameters: { layout: 'fullscreen' },
} satisfies Meta;

export default meta;
type Story = StoryObj<typeof meta>;

const swatchGrid = `
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem">
        <div v-for="name in names" :key="name"
            style="border:1px solid var(--color-border);border-radius:.5rem;overflow:hidden;font-size:.75rem">
            <div :style="{ height:'64px', background:'var(--color-'+name+')' }"></div>
            <div style="padding:.5rem 0.625rem">
                <div style="font-weight:600">{{ name }}</div>
                <div style="color:var(--color-muted-foreground);font-family:var(--font-mono)">--color-{{ name }}</div>
            </div>
        </div>
    </div>`;

export const Semantic: Story = {
    render: () => ({
        setup: () => ({ names: semantic }),
        template: swatchGrid,
    }),
};

export const Brand: Story = {
    render: () => ({
        setup: () => ({ names: brand }),
        template: swatchGrid,
    }),
};
