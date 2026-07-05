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

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <header class="mx-auto w-full max-w-5xl p-6 lg:p-8">
            <a href="/" class="flex items-center gap-2 font-semibold">
                <span
                    class="flex size-8 items-center justify-center rounded-md bg-brand-700 text-white"
                >
                    <AppLogoIcon class="size-5" />
                </span>
                NationalParks.me
            </a>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1 px-6 pb-16">
            <div class="mb-8 flex flex-col gap-1">
                <h1 class="text-3xl font-bold tracking-tight">
                    {{ displayName }}'s parks
                </h1>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    {{ visitedCount }} of {{ totalCount }} National Parks
                    visited
                </p>
            </div>

            <ParksMap :parks="parks" class="mb-8" />

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="park in parks"
                    :key="park.id"
                    class="rounded-xl border p-4"
                    :class="
                        park.visited
                            ? 'border-brand-700/30 bg-brand-700/5'
                            : 'border-black/5 opacity-60 dark:border-white/10'
                    "
                >
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="leading-tight font-semibold">
                            {{ park.name }}
                        </h2>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span
                                v-if="park.closed"
                                class="inline-flex items-center gap-1 rounded-full bg-red-500/15 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-400"
                            >
                                <Ban class="size-3" />
                                Closure
                            </span>
                            <span
                                v-if="park.visited"
                                class="inline-flex items-center gap-1 rounded-full bg-brand-700/10 px-2 py-0.5 text-xs font-medium text-brand-700 dark:text-brand-400"
                            >
                                <Check class="size-3" />
                                Visited
                            </span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        {{ park.designation }}
                        <span v-if="park.states.length">
                            · {{ park.states.join(', ') }}</span
                        >
                    </p>
                    <p
                        v-if="park.visited && park.last_visited_at"
                        class="mt-2 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Last visited {{ park.last_visited_at }}
                    </p>
                </div>
            </div>
        </main>

        <footer
            class="mx-auto w-full max-w-5xl p-6 text-center text-sm text-[#706f6c] lg:p-8 dark:text-[#A1A09A]"
        >
            Track your own at
            <a
                href="/"
                class="font-medium text-brand-700 hover:underline dark:text-brand-400"
                >nationalparks.me</a
            >
        </footer>
    </div>
</template>
