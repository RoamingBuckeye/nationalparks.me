<script setup lang="ts">
import { Head, Link, router, setLayoutProps, useForm } from '@inertiajs/vue3';
import { CalendarPlus, ExternalLink, MapPin, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ParkAlerts from '@/components/organisms/ParkAlerts.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as parksIndex, show as parkShow } from '@/routes/parks';
import { destroy as visitDestroy, store as visitStore } from '@/routes/visits';
import { show as visitShow } from '@/routes/visits';
import type { ParkAlert } from '@/types';

type Park = {
    id: number;
    park_code: string;
    name: string;
    full_name: string | null;
    designation: string | null;
    description: string | null;
    url: string | null;
    directions_url: string | null;
    weather_info: string | null;
    latitude: number | null;
    longitude: number | null;
    states: { code: string; name: string }[];
};

type PoiCount = { value: string; label: string; count: number };

type VisitSummary = {
    id: number;
    started_at: string;
    ended_at: string | null;
    is_live: boolean;
    pois_count: number;
    has_notes: boolean;
};

const props = defineProps<{
    park: Park;
    poiCounts: PoiCount[];
    alerts: ParkAlert[];
    visits: VisitSummary[];
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Parks', href: parksIndex() },
        { title: props.park.name, href: parkShow(props.park.id) },
    ],
});

const checkingIn = ref(false);

const checkInNow = (): void => {
    router.post(
        visitStore().url,
        { park_id: props.park.id },
        {
            onStart: () => (checkingIn.value = true),
            onFinish: () => (checkingIn.value = false),
        },
    );
};

const showPastForm = ref(false);

const pastForm = useForm({
    park_id: props.park.id,
    started_at: '',
    ended_at: '',
    notes: '',
});

const submitPastVisit = (): void => {
    pastForm.post(visitStore().url, {
        onSuccess: () => {
            showPastForm.value = false;
            pastForm.reset();
        },
    });
};

const deleteVisit = (id: number): void => {
    if (
        confirm(
            'Delete this visit? This also removes its checked points of interest.',
        )
    ) {
        router.delete(visitDestroy(id).url, { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="park.name" />

    <div class="page page--gap-6">
        <header class="park-header">
            <h1 class="page-title">
                {{ park.name }}
            </h1>
            <p class="page-desc">
                {{ park.designation }}
                <span v-if="park.states.length">
                    · {{ park.states.map((s) => s.name).join(', ') }}</span
                >
            </p>
            <div class="park-links">
                <a
                    v-if="park.url"
                    :href="park.url"
                    target="_blank"
                    rel="noopener"
                    class="park-link"
                >
                    <ExternalLink /> NPS park page
                </a>
                <a
                    v-if="park.directions_url"
                    :href="park.directions_url"
                    target="_blank"
                    rel="noopener"
                    class="park-link"
                >
                    <MapPin /> Directions
                </a>
            </div>
        </header>

        <p v-if="park.description" class="park-description">
            {{ park.description }}
        </p>

        <div class="park-poi-grid">
            <Card v-for="count in poiCounts" :key="count.value">
                <CardContent class="park-poi-body">
                    <p class="park-poi-count">{{ count.count }}</p>
                    <p class="park-poi-label">
                        {{ count.label }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <ParkAlerts :alerts="alerts" />

        <section class="park-visits">
            <div class="page-topbar">
                <h2 class="park-visits-title">Your visits</h2>
                <div class="park-visit-actions">
                    <Button :disabled="checkingIn" @click="checkInNow">
                        <MapPin /> Check in now
                    </Button>
                    <Button
                        variant="outline"
                        @click="showPastForm = !showPastForm"
                    >
                        <CalendarPlus /> Log a past visit
                    </Button>
                </div>
            </div>

            <Card v-if="showPastForm">
                <CardContent class="park-form-body">
                    <form class="park-form" @submit.prevent="submitPastVisit">
                        <div class="park-form-grid">
                            <div class="park-form-field">
                                <Label for="started_at">Arrived</Label>
                                <Input
                                    id="started_at"
                                    v-model="pastForm.started_at"
                                    type="date"
                                    required
                                />
                                <p
                                    v-if="pastForm.errors.started_at"
                                    class="park-form-error"
                                >
                                    {{ pastForm.errors.started_at }}
                                </p>
                            </div>
                            <div class="park-form-field">
                                <Label for="ended_at"
                                    >Left
                                    <span class="park-optional"
                                        >(optional)</span
                                    ></Label
                                >
                                <Input
                                    id="ended_at"
                                    v-model="pastForm.ended_at"
                                    type="date"
                                />
                                <p
                                    v-if="pastForm.errors.ended_at"
                                    class="park-form-error"
                                >
                                    {{ pastForm.errors.ended_at }}
                                </p>
                            </div>
                        </div>
                        <div class="park-form-field">
                            <Label for="notes"
                                >Journal
                                <span class="park-optional"
                                    >(optional)</span
                                ></Label
                            >
                            <textarea
                                id="notes"
                                v-model="pastForm.notes"
                                rows="3"
                                placeholder="What stood out about this trip?"
                                class="park-textarea"
                            />
                        </div>
                        <div class="park-form-actions">
                            <Button
                                type="submit"
                                :disabled="pastForm.processing"
                            >
                                Save visit
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                @click="showPastForm = false"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div v-if="visits.length === 0" class="parks-empty">
                You haven't logged a visit to this park yet.
            </div>

            <div v-else class="park-visit-list">
                <Card v-for="visit in visits" :key="visit.id">
                    <CardContent class="park-visit-body">
                        <Link
                            :href="visitShow(visit.id)"
                            class="park-visit-link"
                        >
                            <span class="park-visit-title">
                                {{ visit.started_at }}
                                <span v-if="visit.ended_at">
                                    – {{ visit.ended_at }}</span
                                >
                                <span
                                    v-if="visit.is_live"
                                    class="park-visit-live"
                                >
                                    Live
                                </span>
                            </span>
                            <span class="park-visit-meta">
                                {{ visit.pois_count }} checked off
                                <span v-if="visit.has_notes"> · Journal</span>
                            </span>
                        </Link>
                        <Button
                            variant="ghost"
                            size="icon"
                            aria-label="Delete visit"
                            @click="deleteVisit(visit.id)"
                        >
                            <Trash2 class="park-visit-delete-icon" />
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>
