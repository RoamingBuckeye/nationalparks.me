import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import StampsIndex from '@/pages/stamps/Index.vue';
import type { Stamp as StampType } from '@/types/stamps';

// The page pulls in Inertia's <Head>, which needs a running Inertia app; stub
// it so we can mount the page in isolation. (vi.mock is hoisted above imports.)
vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
}));

function stamp(
    category: string,
    overrides: Partial<StampType> = {},
): StampType {
    return {
        id: Math.floor(Math.random() * 1e6),
        slug: 'slug',
        name: 'A Stamp',
        description: null,
        scene: null,
        accent_color: '#2f7d46',
        category,
        earned: false,
        progress: 0,
        required: 1,
        earned_at: null,
        vintage_year: null,
        ...overrides,
    };
}

function mountPage(stamps: StampType[]) {
    return mount(StampsIndex, {
        props: {
            stamps,
            earnedCount: stamps.filter((s) => s.earned).length,
            totalCount: stamps.length,
        },
        global: { stubs: { Head: true, Stamp: true } },
    });
}

describe('stamps/Index', () => {
    it('groups stamps into tiers in reading order', () => {
        const wrapper = mountPage([
            stamp('Regions', { name: 'Southeast' }),
            stamp('Milestones', { name: 'First Stamp', earned: true }),
            stamp('State Collections', { name: 'Buckeye' }),
        ]);

        const headings = wrapper.findAll('h2').map((h) => h.text());
        expect(headings).toEqual([
            'Milestones',
            'State Collections',
            'Regions',
        ]);
    });

    it('omits tiers that have no stamps', () => {
        const wrapper = mountPage([
            stamp('Milestones', { name: 'First Stamp' }),
        ]);

        expect(wrapper.findAll('h2').map((h) => h.text())).toEqual([
            'Milestones',
        ]);
    });

    it('shows an earned/total count per tier', () => {
        const wrapper = mountPage([
            stamp('Milestones', { name: 'First Stamp', earned: true }),
            stamp('Milestones', { name: 'High Five', earned: false }),
        ]);

        // The tier heading is followed by its "earned/total" tally.
        expect(wrapper.text()).toContain('1/2');
    });
});
