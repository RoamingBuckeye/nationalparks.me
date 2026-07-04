<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Camera, ListChecks, MapPin, Mountain } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as parksIndex } from '@/routes/parks';
import { show as visitShow } from '@/routes/visits';

type RecentVisit = {
    id: number;
    park_name: string;
    started_at: string;
    is_live: boolean;
};

const props = defineProps<{
    stats: { parks_visited: number; pois_checked: number; photos: number };
    recentVisits: RecentVisit[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage();
const greetingName = computed(
    () => page.props.auth.user.display_name || page.props.auth.user.name,
);

const stats = computed(() => [
    {
        label: 'Parks visited',
        value: `${props.stats.parks_visited} / 63`,
        icon: Mountain,
    },
    {
        label: 'Points of interest',
        value: `${props.stats.pois_checked}`,
        icon: ListChecks,
    },
    { label: 'Photos', value: `${props.stats.photos}`, icon: Camera },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Welcome back, {{ greetingName }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    Here's your National Parks progress at a glance.
                </p>
            </div>
            <Button as-child>
                <Link :href="parksIndex()">
                    <Mountain class="size-4" /> Browse parks
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card v-for="stat in stats" :key="stat.label">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ stat.label }}
                    </CardTitle>
                    <component
                        :is="stat.icon"
                        class="size-5 text-brand-700 dark:text-brand-400"
                    />
                </CardHeader>
                <CardContent>
                    <p class="font-mono text-3xl font-semibold tabular-nums">
                        {{ stat.value }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card class="flex-1">
            <CardHeader>
                <CardTitle class="text-base">Recent visits</CardTitle>
            </CardHeader>
            <CardContent>
                <div
                    v-if="recentVisits.length === 0"
                    class="flex flex-col items-center justify-center gap-3 py-12 text-center"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-full bg-brand-700/10 text-brand-700 dark:text-brand-400"
                    >
                        <MapPin class="size-6" />
                    </div>
                    <h2 class="text-lg font-semibold">No visits logged yet</h2>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Check in to a park to start tracking your visits, points
                        of interest, and journal entries.
                    </p>
                    <Button as-child variant="outline">
                        <Link :href="parksIndex()">Browse parks</Link>
                    </Button>
                </div>

                <ul v-else class="flex flex-col gap-1">
                    <li v-for="visit in recentVisits" :key="visit.id">
                        <Link
                            :href="visitShow(visit.id)"
                            class="flex items-center justify-between gap-3 rounded-md px-3 py-2 hover:bg-muted"
                        >
                            <span class="flex items-center gap-2 font-medium">
                                {{ visit.park_name }}
                                <span
                                    v-if="visit.is_live"
                                    class="rounded-full bg-brand-700/10 px-2 py-0.5 text-xs font-medium text-brand-700 dark:text-brand-400"
                                >
                                    Live
                                </span>
                            </span>
                            <span class="text-sm text-muted-foreground">
                                {{ visit.started_at }}
                            </span>
                        </Link>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
