import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';

const meta = {
    title: 'Atoms/Feedback',
    tags: ['autodocs'],
} satisfies Meta;

export default meta;
type Story = StoryObj<typeof meta>;

export const LoadingSpinner: Story = {
    render: () => ({
        components: { Spinner },
        template: '<Spinner />',
    }),
};

export const SkeletonLines: Story = {
    render: () => ({
        components: { Skeleton },
        template: `
            <div style="display:flex;flex-direction:column;gap:.5rem;max-width:20rem">
                <Skeleton style="height:1rem;width:80%" />
                <Skeleton style="height:1rem;width:60%" />
                <Skeleton style="height:1rem;width:70%" />
            </div>`,
    }),
};

export const Divider: Story = {
    render: () => ({
        components: { Separator },
        template: `
            <div style="max-width:20rem">
                <p>Above</p>
                <Separator style="margin:.75rem 0" />
                <p>Below</p>
            </div>`,
    }),
};

export const AvatarFallbackInitials: Story = {
    render: () => ({
        components: { Avatar, AvatarFallback },
        template: `
            <Avatar>
                <AvatarFallback style="background:var(--color-secondary);font-weight:600">TU</AvatarFallback>
            </Avatar>`,
    }),
};
