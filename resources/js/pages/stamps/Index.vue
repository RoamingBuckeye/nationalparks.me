<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Stamp from '@/components/Stamp.vue';
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

    <div class="flex h-full flex-1 flex-col gap-8 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Stamps</h1>
            <p class="text-sm text-muted-foreground">
                {{ earnedCount }} of {{ totalCount }} earned — check into parks
                to collect them.
            </p>
        </div>

        <section
            v-for="group in groups"
            :key="group.category"
            class="flex flex-col gap-4"
        >
            <div class="flex items-baseline gap-3 border-b pb-2">
                <h2 class="text-lg font-semibold tracking-tight">
                    {{ group.category }}
                </h2>
                <span class="text-sm text-muted-foreground tabular-nums">
                    {{ group.earned }}/{{ group.stamps.length }}
                </span>
            </div>

            <div
                class="grid justify-items-center gap-x-4 gap-y-8"
                style="
                    grid-template-columns: repeat(
                        auto-fill,
                        minmax(8.5rem, 1fr)
                    );
                "
            >
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
