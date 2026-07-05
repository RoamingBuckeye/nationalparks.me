<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ParksMap from '@/components/organisms/ParksMap.vue';
import type { MapPark } from '@/components/organisms/ParksMap.vue';
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

    <div class="page page--gap-4">
        <div class="page-topbar">
            <div>
                <h1 class="page-title">Map</h1>
                <p class="page-desc">
                    {{ visitedCount }} of {{ totalCount }} parks visited
                </p>
            </div>
            <div class="map-legend">
                <span class="map-legend-item">
                    <span class="map-dot map-dot--visited" />
                    Visited
                </span>
                <span class="map-legend-item">
                    <span class="map-dot map-dot--unvisited" />
                    Not visited
                </span>
                <span class="map-legend-item">
                    <span class="map-dot map-dot--closure" />
                    Active closure
                </span>
            </div>
        </div>

        <ParksMap :parks="parks" />
    </div>
</template>
