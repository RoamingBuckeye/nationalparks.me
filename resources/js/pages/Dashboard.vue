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

    <div class="page page--gap-6">
        <div class="page-topbar">
            <div>
                <h1 class="page-title">Welcome back, {{ greetingName }}</h1>
                <p class="page-desc">
                    Here's your National Parks progress at a glance.
                </p>
            </div>
            <Button as-child>
                <Link :href="parksIndex()"> <Mountain /> Browse parks </Link>
            </Button>
        </div>

        <div class="dashboard-stats">
            <Card v-for="stat in stats" :key="stat.label">
                <CardHeader class="dashboard-stat-header">
                    <CardTitle class="dashboard-stat-label">
                        {{ stat.label }}
                    </CardTitle>
                    <component :is="stat.icon" class="dashboard-stat-icon" />
                </CardHeader>
                <CardContent>
                    <p class="dashboard-stat-value">
                        {{ stat.value }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card class="dashboard-recent">
            <CardHeader>
                <CardTitle class="dashboard-recent-title"
                    >Recent visits</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div v-if="recentVisits.length === 0" class="dashboard-empty">
                    <div class="dashboard-empty-icon-box">
                        <MapPin class="dashboard-empty-icon" />
                    </div>
                    <h2 class="dashboard-empty-title">No visits logged yet</h2>
                    <p class="dashboard-empty-text">
                        Check in to a park to start tracking your visits, points
                        of interest, and journal entries.
                    </p>
                    <Button as-child variant="outline">
                        <Link :href="parksIndex()">Browse parks</Link>
                    </Button>
                </div>

                <ul v-else class="dashboard-visits">
                    <li v-for="visit in recentVisits" :key="visit.id">
                        <Link
                            :href="visitShow(visit.id)"
                            class="dashboard-visit"
                        >
                            <span class="dashboard-visit-name">
                                {{ visit.park_name }}
                                <span
                                    v-if="visit.is_live"
                                    class="dashboard-visit-live"
                                >
                                    Live
                                </span>
                            </span>
                            <span class="dashboard-visit-date">
                                {{ visit.started_at }}
                            </span>
                        </Link>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
