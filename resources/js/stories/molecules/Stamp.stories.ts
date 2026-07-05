import type { Meta, StoryObj } from '@storybook/vue3-vite';
import Stamp from '@/components/molecules/Stamp.vue';
import type { Stamp as StampType } from '@/types/stamps';

const earned: StampType = {
    id: 1,
    slug: 'first-stamp',
    name: 'First Stamp',
    description: 'Check in to your first park.',
    scene: null,
    accent_color: '#2f7d46',
    category: 'Milestones',
    earned: true,
    progress: 1,
    required: 1,
    earned_at: '2026-06-30',
    vintage_year: 2026,
};

const locked: StampType = {
    id: 2,
    slug: 'the-63-club',
    name: 'The 63 Club',
    description: 'Visit all 63 national parks.',
    scene: null,
    accent_color: '#e6b325',
    category: 'Milestones',
    earned: false,
    progress: 12,
    required: 63,
    earned_at: null,
    vintage_year: null,
};

const meta = {
    title: 'Molecules/Stamp',
    component: Stamp,
    tags: ['autodocs'],
} satisfies Meta<typeof Stamp>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Earned: Story = { args: { stamp: earned } };

export const Locked: Story = { args: { stamp: locked } };

export const Collection: Story = {
    render: () => ({
        components: { Stamp },
        setup: () => ({ earned, locked }),
        template: `
            <div style="display:flex;gap:1.5rem;flex-wrap:wrap">
                <Stamp :stamp="earned" />
                <Stamp :stamp="locked" />
            </div>`,
    }),
};
