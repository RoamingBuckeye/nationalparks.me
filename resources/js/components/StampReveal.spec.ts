import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import StampReveal from '@/components/StampReveal.vue';
import type { EarnedStamp } from '@/types/stamps';

// Capture the flash handler Inertia's router registers so tests can fire it.
// (vi.mock and vi.hoisted are hoisted above the imports above.)
const { handlers } = vi.hoisted(() => ({
    handlers: {} as Record<string, (event: unknown) => void>,
}));

vi.mock('@inertiajs/vue3', () => ({
    router: {
        on: (event: string, callback: (event: unknown) => void) => {
            handlers[event] = callback;

            return () => delete handlers[event];
        },
    },
}));

function fireFlash(payload: unknown) {
    handlers.flash?.({ detail: { flash: payload } });
}

function earnedStamp(id: number, name: string): EarnedStamp {
    return {
        id,
        slug: `stamp-${id}`,
        name,
        description: null,
        scene: null,
        accent_color: '#2f7d46',
    };
}

const stubs = {
    Dialog: {
        props: ['open'],
        template: '<div v-if="open"><slot /></div>',
    },
    DialogContent: { template: '<div><slot /></div>' },
    DialogHeader: { template: '<div><slot /></div>' },
    DialogTitle: { template: '<h2><slot /></h2>' },
    DialogDescription: { template: '<p><slot /></p>' },
    DialogFooter: { template: '<div><slot /></div>' },
    Button: { template: '<button><slot /></button>' },
    Stamp: { props: ['stamp'], template: '<div class="stamp-stub" />' },
};

describe('StampReveal', () => {
    it('opens and celebrates a single earned stamp', async () => {
        const wrapper = mount(StampReveal, { global: { stubs } });
        fireFlash({ stampsEarned: [earnedStamp(1, 'First Stamp')] });
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('New stamp earned!');
        expect(wrapper.findAll('.stamp-stub')).toHaveLength(1);
    });

    it('pluralizes the copy for multiple stamps', async () => {
        const wrapper = mount(StampReveal, { global: { stubs } });
        fireFlash({
            stampsEarned: [
                earnedStamp(1, 'First Stamp'),
                earnedStamp(2, 'Mountaineer'),
            ],
        });
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('New stamps earned!');
        expect(wrapper.text()).toContain('2 stamps');
        expect(wrapper.findAll('.stamp-stub')).toHaveLength(2);
    });

    it('stays closed for a flash that carries no stamps', async () => {
        const wrapper = mount(StampReveal, { global: { stubs } });
        fireFlash({ toast: { type: 'success', message: 'Saved' } });
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).not.toContain('earned');
    });
});
