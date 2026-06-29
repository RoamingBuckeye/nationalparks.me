<script setup lang="ts">
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onBeforeUnmount, onMounted, ref } from 'vue';

export type MapPark = {
    id: number;
    name: string;
    latitude: number | null;
    longitude: number | null;
    visited: boolean;
    visits_count: number;
    last_visited_at: string | null;
    href?: string;
};

const props = defineProps<{ parks: MapPark[] }>();

const mapEl = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;

const buildPopup = (park: MapPark): HTMLElement => {
    const container = document.createElement('div');
    container.className = 'space-y-0.5';

    const title = document.createElement('p');
    title.className = 'font-semibold';
    title.textContent = park.name;
    container.appendChild(title);

    const status = document.createElement('p');
    status.textContent = park.visited
        ? `Visited · ${park.visits_count} ${park.visits_count === 1 ? 'visit' : 'visits'}`
        : 'Not visited yet';
    container.appendChild(status);

    if (park.visited && park.last_visited_at) {
        const last = document.createElement('p');
        last.textContent = `Last visited ${park.last_visited_at}`;
        container.appendChild(last);
    }

    if (park.href) {
        const link = document.createElement('a');
        link.href = park.href;
        link.textContent = 'View park →';
        link.className = 'text-emerald-700 underline';
        container.appendChild(link);
    }

    return container;
};

onMounted(() => {
    if (!mapEl.value) {
        return;
    }

    const instance = L.map(mapEl.value, { worldCopyJump: true }).setView(
        [38, -96],
        3,
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(instance);

    props.parks.forEach((park) => {
        if (
            typeof park.latitude !== 'number' ||
            typeof park.longitude !== 'number'
        ) {
            return;
        }

        L.circleMarker([park.latitude, park.longitude], {
            radius: 6,
            color: '#ffffff',
            weight: 1,
            fillColor: park.visited ? '#15803d' : '#9ca3af',
            fillOpacity: 0.9,
        })
            .addTo(instance)
            .bindPopup(buildPopup(park));
    });

    map = instance;
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div ref="mapEl" class="relative z-0 h-[75vh] w-full rounded-xl border" />
</template>
