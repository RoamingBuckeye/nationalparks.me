<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Ban, Check, MapPin } from '@lucide/vue';
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
    closed: boolean;
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

    <div class="page page--gap-6">
        <div>
            <h1 class="page-title">National Parks</h1>
            <p class="page-desc">
                {{ parks.length }} of 63 parks shown — check in to the ones
                you've visited.
            </p>
        </div>

        <div class="parks-filters">
            <Input
                v-model="search"
                type="search"
                placeholder="Search parks…"
                aria-label="Search parks"
                class="parks-search"
            />
            <select
                v-model="visited"
                aria-label="Filter by visited status"
                class="parks-select"
            >
                <option value="">All parks</option>
                <option value="visited">Visited</option>
                <option value="unvisited">Not visited</option>
            </select>
            <select
                v-model="state"
                aria-label="Filter by state"
                class="parks-select"
            >
                <option value="">All states</option>
                <option v-for="s in states" :key="s.code" :value="s.code">
                    {{ s.name }}
                </option>
            </select>
        </div>

        <div v-if="parks.length === 0" class="parks-empty parks-empty--roomy">
            No parks match your filters.
        </div>

        <div v-else class="parks-grid">
            <Link
                v-for="park in parks"
                :key="park.id"
                :href="parkShow(park.id)"
                class="park-card-link"
            >
                <Card class="park-card">
                    <CardContent class="park-card-body">
                        <div class="park-card-top">
                            <h2 class="park-card-name">
                                {{ park.name }}
                            </h2>
                            <div class="park-card-badges">
                                <span
                                    v-if="park.closed"
                                    class="park-badge park-badge--closure"
                                >
                                    <Ban />
                                    Closure
                                </span>
                                <span
                                    v-if="park.visits_count > 0"
                                    class="park-badge park-badge--visited"
                                >
                                    <Check />
                                    Visited
                                </span>
                            </div>
                        </div>
                        <p class="park-card-meta">
                            {{ park.designation }}
                            <span v-if="park.states.length">
                                · {{ park.states.join(', ') }}</span
                            >
                        </p>
                        <div class="park-card-stats">
                            <span
                                v-if="park.visits_count > 0"
                                class="park-card-stat"
                            >
                                <MapPin />
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
