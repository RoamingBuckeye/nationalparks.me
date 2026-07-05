import type { Meta, StoryObj } from '@storybook/vue3-vite';
import InputError from '@/components/atoms/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const meta = {
    title: 'Atoms/Form field',
    tags: ['autodocs'],
} satisfies Meta;

export default meta;
type Story = StoryObj<typeof meta>;

export const TextInput: Story = {
    render: () => ({
        components: { Label, Input },
        template: `
            <div style="display:grid;gap:.5rem;max-width:20rem">
                <Label for="email">Email address</Label>
                <Input id="email" type="email" placeholder="email@example.com" />
            </div>`,
    }),
};

export const WithError: Story = {
    render: () => ({
        components: { Label, Input, InputError },
        template: `
            <div style="display:grid;gap:.5rem;max-width:20rem">
                <Label for="email2">Email address</Label>
                <Input id="email2" type="email" value="not-an-email" />
                <InputError message="Enter a valid email address." />
            </div>`,
    }),
};

export const Checkboxes: Story = {
    render: () => ({
        components: { Label, Checkbox },
        template: `
            <div style="display:flex;flex-direction:column;gap:.75rem">
                <Label style="display:flex;align-items:center;gap:.5rem">
                    <Checkbox :model-value="true" /> Visited
                </Label>
                <Label style="display:flex;align-items:center;gap:.5rem">
                    <Checkbox :model-value="false" /> Not visited
                </Label>
            </div>`,
    }),
};
