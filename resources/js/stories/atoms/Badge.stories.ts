import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { Badge } from '@/components/ui/badge';

const meta = {
    title: 'Atoms/Badge',
    component: Badge,
    tags: ['autodocs'],
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'secondary', 'destructive', 'outline'],
        },
    },
    args: { variant: 'default' },
    render: (args) => ({
        components: { Badge },
        setup: () => ({ args }),
        template: '<Badge v-bind="args">Badge</Badge>',
    }),
} satisfies Meta<typeof Badge>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Playground: Story = {};

export const Variants: Story = {
    render: () => ({
        components: { Badge },
        template: `
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <Badge variant="default">Default</Badge>
                <Badge variant="secondary">Secondary</Badge>
                <Badge variant="destructive">Destructive</Badge>
                <Badge variant="outline">Outline</Badge>
            </div>`,
    }),
};
