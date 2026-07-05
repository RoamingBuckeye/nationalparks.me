<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Stamp from '@/components/molecules/Stamp.vue';
import { stamps as stampsRoute } from '@/routes';
import type { Stamp as StampType } from '@/types';

const props = defineProps<{
    stamps: StampType[];
    earnedCount: number;
    totalCount: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Stamps', href: stampsRoute() }],
    },
});

// Tiers, in the order they should read on the page.
const CATEGORY_ORDER = ['Milestones', 'State Collections', 'Regions'];

const groups = computed(() =>
    CATEGORY_ORDER.map((category) => {
        const stamps = props.stamps.filter(
            (stamp) => stamp.category === category,
        );

        return {
            category,
            stamps,
            earned: stamps.filter((stamp) => stamp.earned).length,
        };
    }).filter((group) => group.stamps.length > 0),
);
</script>

<template>
    <Head title="Stamps" />

    <div class="page page--gap-8">
        <div>
            <h1 class="page-title">Stamps</h1>
            <p class="page-desc">
                {{ earnedCount }} of {{ totalCount }} earned — check into parks
                to collect them.
            </p>
        </div>

        <section
            v-for="group in groups"
            :key="group.category"
            class="stamps-group"
        >
            <div class="stamps-group-header">
                <h2 class="stamps-group-title">
                    {{ group.category }}
                </h2>
                <span class="stamps-group-count">
                    {{ group.earned }}/{{ group.stamps.length }}
                </span>
            </div>

            <div class="stamps-grid">
                <Stamp
                    v-for="stamp in group.stamps"
                    :key="stamp.id"
                    :stamp="stamp"
                    :title="stamp.description ?? undefined"
                />
            </div>
        </section>
    </div>
</template>
