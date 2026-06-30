<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ParksMap from '@/components/ParksMap.vue';
import type { MapPark } from '@/components/ParksMap.vue';
import { map as mapRoute } from '@/routes';

defineProps<{
    parks: MapPark[];
    visitedCount: number;
    totalCount: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Map', href: mapRoute() }],
    },
});
</script>

<template>
    <Head title="Map" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Map</h1>
                <p class="text-sm text-muted-foreground">
                    {{ visitedCount }} of {{ totalCount }} parks visited
                </p>
            </div>
            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <span class="size-3 rounded-full bg-brand-700" />
                    Visited
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="size-3 rounded-full bg-gray-400" />
                    Not visited
                </span>
            </div>
        </div>

        <ParksMap :parks="parks" />
    </div>
</template>
