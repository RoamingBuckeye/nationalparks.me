import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { Button } from '@/components/ui/button';

const meta = {
    title: 'Atoms/Button',
    component: Button,
    tags: ['autodocs'],
    argTypes: {
        variant: {
            control: 'select',
            options: [
                'default',
                'secondary',
                'outline',
                'ghost',
                'destructive',
                'link',
            ],
        },
        size: {
            control: 'select',
            options: ['default', 'sm', 'lg'],
        },
    },
    args: { variant: 'default', size: 'default' },
    render: (args) => ({
        components: { Button },
        setup: () => ({ args }),
        template: '<Button v-bind="args">Button</Button>',
    }),
} satisfies Meta<typeof Button>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Playground: Story = {};

export const Variants: Story = {
    render: () => ({
        components: { Button },
        template: `
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
                <Button variant="default">Default</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="outline">Outline</Button>
                <Button variant="ghost">Ghost</Button>
                <Button variant="destructive">Destructive</Button>
                <Button variant="link">Link</Button>
            </div>`,
    }),
};

export const Sizes: Story = {
    render: () => ({
        components: { Button },
        template: `
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
                <Button size="sm">Small</Button>
                <Button size="default">Default</Button>
                <Button size="lg">Large</Button>
            </div>`,
    }),
};
