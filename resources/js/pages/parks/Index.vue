<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, MapPin } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { index as parksIndex, show as parkShow } from '@/routes/parks';

type ParkListItem = {
    id: number;
    park_code: string;
    name: string;
    designation: string | null;
    states: string[];
    visits_count: number;
    last_visited_at: string | null;
};

type StateOption = { code: string; name: string };

const props = defineProps<{
    parks: ParkListItem[];
    filters: { search: string; visited: string; state: string };
    states: StateOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Parks', href: parksIndex() }],
    },
});

const search = ref(props.filters.search);
const visited = ref(props.filters.visited);
const state = ref(props.filters.state);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilters = (): void => {
    router.get(
        parksIndex().url,
        { search: search.value, visited: visited.value, state: state.value },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch([visited, state], applyFilters);
</script>

<template>
    <Head title="Parks" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                National Parks
            </h1>
            <p class="text-sm text-muted-foreground">
                {{ parks.length }} of 63 parks shown — check in to the ones
                you've visited.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <Input
                v-model="search"
                type="search"
                placeholder="Search parks…"
                class="sm:max-w-xs"
            />
            <select
                v-model="visited"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs"
            >
                <option value="">All parks</option>
                <option value="visited">Visited</option>
                <option value="unvisited">Not visited</option>
            </select>
            <select
                v-model="state"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs"
            >
                <option value="">All states</option>
                <option v-for="s in states" :key="s.code" :value="s.code">
                    {{ s.name }}
                </option>
            </select>
        </div>

        <div
            v-if="parks.length === 0"
            class="rounded-xl border border-dashed p-12 text-center text-sm text-muted-foreground"
        >
            No parks match your filters.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="park in parks"
                :key="park.id"
                :href="parkShow(park.id)"
                class="block transition-transform hover:-translate-y-0.5"
            >
                <Card class="h-full">
                    <CardContent class="flex h-full flex-col gap-2 p-5">
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="leading-tight font-semibold">
                                {{ park.name }}
                            </h2>
                            <span
                                v-if="park.visits_count > 0"
                                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-700/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400"
                            >
                                <Check class="size-3" />
                                Visited
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ park.designation }}
                            <span v-if="park.states.length">
                                · {{ park.states.join(', ') }}</span
                            >
                        </p>
                        <div
                            class="mt-auto flex items-center gap-3 text-xs text-muted-foreground"
                        >
                            <span
                                v-if="park.visits_count > 0"
                                class="inline-flex items-center gap-1"
                            >
                                <MapPin class="size-3" />
                                {{ park.visits_count }}
                                {{
                                    park.visits_count === 1 ? 'visit' : 'visits'
                                }}
                            </span>
                            <span v-if="park.last_visited_at">
                                Last: {{ park.last_visited_at }}
                            </span>
                            <span v-if="park.visits_count === 0">
                                Not visited yet
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
