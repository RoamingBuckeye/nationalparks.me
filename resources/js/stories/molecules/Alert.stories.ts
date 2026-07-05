import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const meta = {
    title: 'Molecules/Alert',
    component: Alert,
    tags: ['autodocs'],
    argTypes: {
        variant: { control: 'select', options: ['default', 'destructive'] },
    },
    args: { variant: 'default' },
    render: (args) => ({
        components: { Alert, AlertTitle, AlertDescription, AlertCircle },
        setup: () => ({ args }),
        template: `
            <Alert v-bind="args" style="max-width:28rem">
                <AlertCircle />
                <AlertTitle>Heads up</AlertTitle>
                <AlertDescription>
                    This park has an active weather advisory.
                </AlertDescription>
            </Alert>`,
    }),
} satisfies Meta<typeof Alert>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const Destructive: Story = { args: { variant: 'destructive' } };
