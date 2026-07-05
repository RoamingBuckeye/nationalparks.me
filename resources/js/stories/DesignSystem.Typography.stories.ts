import type { Meta, StoryObj } from '@storybook/vue3-vite';

const meta = {
    title: 'Design System/Typography',
    parameters: { layout: 'fullscreen' },
} satisfies Meta;

export default meta;
type Story = StoryObj<typeof meta>;

export const Faces: Story = {
    render: () => ({
        template: `
        <div style="display:flex;flex-direction:column;gap:1.5rem;max-width:44rem">
            <div>
                <div style="font-size:.75rem;color:var(--color-muted-foreground);font-family:var(--font-mono)">--font-serif · Fraunces (h1/h2 display)</div>
                <div style="font-family:var(--font-serif);font-size:2.25rem;font-weight:600">Every park. One map.</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--color-muted-foreground);font-family:var(--font-mono)">--font-sans · Instrument Sans (body)</div>
                <div style="font-size:1rem">Check in to the parks you've explored and keep a journal of every trip.</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--color-muted-foreground);font-family:var(--font-mono)">--font-mono · data (stats, codes)</div>
                <div style="font-family:var(--font-mono);font-variant-numeric:tabular-nums;font-size:1.5rem;font-weight:600">12 / 63</div>
            </div>
        </div>`,
    }),
};

export const Scale: Story = {
    render: () => ({
        setup: () => ({
            sizes: [
                { label: '3xl', px: '1.875rem' },
                { label: '2xl', px: '1.5rem' },
                { label: 'xl', px: '1.25rem' },
                { label: 'lg', px: '1.125rem' },
                { label: 'base', px: '1rem' },
                { label: 'sm', px: '0.875rem' },
                { label: 'xs', px: '0.75rem' },
            ],
        }),
        template: `
        <div style="display:flex;flex-direction:column;gap:.75rem">
            <div v-for="s in sizes" :key="s.label" style="display:flex;align-items:baseline;gap:1rem">
                <span style="width:3rem;font-family:var(--font-mono);font-size:.75rem;color:var(--color-muted-foreground)">{{ s.label }}</span>
                <span :style="{ fontSize: s.px }">The quick brown fox</span>
            </div>
        </div>`,
    }),
};
