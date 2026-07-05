<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Ban, Check } from '@lucide/vue';
import AppLogoIcon from '@/components/atoms/AppLogoIcon.vue';
import ParksMap from '@/components/organisms/ParksMap.vue';

type SharedPark = {
    id: number;
    name: string;
    designation: string | null;
    states: string[];
    latitude: number | null;
    longitude: number | null;
    visited: boolean;
    visits_count: number;
    last_visited_at: string | null;
    closed: boolean;
};

defineProps<{
    displayName: string;
    visitedCount: number;
    totalCount: number;
    parks: SharedPark[];
}>();
</script>

<template>
    <Head :title="`${displayName}'s National Parks`">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="landing">
        <header class="landing-header">
            <a href="/" class="landing-brand">
                <span class="landing-brand-chip">
                    <AppLogoIcon class="landing-brand-icon" />
                </span>
                NationalParks.me
            </a>
        </header>

        <main class="share-main">
            <div class="share-heading">
                <h1 class="share-title">{{ displayName }}'s parks</h1>
                <p class="share-subtitle">
                    {{ visitedCount }} of {{ totalCount }} National Parks
                    visited
                </p>
            </div>

            <ParksMap :parks="parks" class="share-map" />

            <div class="share-grid">
                <div
                    v-for="park in parks"
                    :key="park.id"
                    class="share-card"
                    :class="
                        park.visited
                            ? 'share-card--visited'
                            : 'share-card--unvisited'
                    "
                >
                    <div class="share-card-top">
                        <h2 class="share-card-name">
                            {{ park.name }}
                        </h2>
                        <div class="share-card-badges">
                            <span
                                v-if="park.closed"
                                class="park-badge park-badge--closure"
                            >
                                <Ban />
                                Closure
                            </span>
                            <span
                                v-if="park.visited"
                                class="park-badge park-badge--visited"
                            >
                                <Check />
                                Visited
                            </span>
                        </div>
                    </div>
                    <p class="share-card-meta">
                        {{ park.designation }}
                        <span v-if="park.states.length">
                            · {{ park.states.join(', ') }}</span
                        >
                    </p>
                    <p
                        v-if="park.visited && park.last_visited_at"
                        class="share-card-last"
                    >
                        Last visited {{ park.last_visited_at }}
                    </p>
                </div>
            </div>
        </main>

        <footer class="landing-footer">
            Track your own at
            <a href="/" class="landing-footer-link">nationalparks.me</a>
        </footer>
    </div>
</template>
