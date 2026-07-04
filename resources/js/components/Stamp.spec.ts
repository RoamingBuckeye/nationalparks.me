import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import Stamp from '@/components/Stamp.vue';
import type { Stamp as StampType } from '@/types/stamps';

function makeStamp(overrides: Partial<StampType> = {}): StampType {
    return {
        id: 1,
        slug: 'state-ut',
        name: 'Mighty Five',
        description: 'Visit every national park in Utah.',
        scene: 'state-ut',
        accent_color: '#e6b325',
        category: 'State Collections',
        earned: false,
        progress: 0,
        required: 5,
        earned_at: null,
        vintage_year: null,
        ...overrides,
    };
}

describe('Stamp', () => {
    it('shows progress and greys out when locked', () => {
        const wrapper = mount(Stamp, {
            props: {
                stamp: makeStamp({ earned: false, progress: 3, required: 5 }),
            },
        });

        expect(wrapper.text()).toContain('3/5');
        expect(wrapper.find('svg').classes()).toContain('grayscale');
    });

    it('shows "Earned" and full color when earned', () => {
        const wrapper = mount(Stamp, {
            props: {
                stamp: makeStamp({ earned: true, progress: 5, required: 5 }),
            },
        });

        expect(wrapper.text()).toContain('Earned');
        expect(wrapper.text()).not.toContain('/');
        expect(wrapper.find('svg').classes()).not.toContain('grayscale');
    });

    it('labels a vintage edition with the year it was earned', () => {
        const wrapper = mount(Stamp, {
            props: { stamp: makeStamp({ earned: true, vintage_year: 2025 }) },
        });

        expect(wrapper.text()).toContain('Earned · 2025');
    });

    it('paints the ring in the accent color', () => {
        const wrapper = mount(Stamp, {
            props: { stamp: makeStamp({ accent_color: '#123456' }) },
        });

        const fills = wrapper
            .findAll('path')
            .map((path) => path.attributes('fill'));
        expect(fills).toContain('#123456');
    });

    it('composes an accessible label from state and progress', () => {
        const locked = mount(Stamp, {
            props: {
                stamp: makeStamp({ earned: false, progress: 2, required: 5 }),
            },
        });
        expect(locked.find('svg').attributes('aria-label')).toBe(
            'Mighty Five stamp, locked — 2 of 5 parks visited',
        );

        const earned = mount(Stamp, {
            props: { stamp: makeStamp({ earned: true, vintage_year: 2025 }) },
        });
        expect(earned.find('svg').attributes('aria-label')).toBe(
            'Mighty Five stamp, earned in 2025',
        );
    });

    it('can hide its label', () => {
        const wrapper = mount(Stamp, {
            props: { stamp: makeStamp(), showLabel: false },
        });

        expect(wrapper.text()).not.toContain('Mighty Five');
    });
});
