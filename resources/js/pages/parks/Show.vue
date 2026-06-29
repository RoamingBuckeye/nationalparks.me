<script setup lang="ts">
import { Head, Link, router, setLayoutProps, useForm } from '@inertiajs/vue3';
import { CalendarPlus, ExternalLink, MapPin, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as parksIndex, show as parkShow } from '@/routes/parks';
import { destroy as visitDestroy, store as visitStore } from '@/routes/visits';
import { show as visitShow } from '@/routes/visits';

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

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <header class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ park.name }}
            </h1>
            <p class="text-sm text-muted-foreground">
                {{ park.designation }}
                <span v-if="park.states.length">
                    · {{ park.states.map((s) => s.name).join(', ') }}</span
                >
            </p>
            <div class="flex flex-wrap gap-3 text-sm">
                <a
                    v-if="park.url"
                    :href="park.url"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1 text-emerald-700 hover:underline dark:text-emerald-400"
                >
                    <ExternalLink class="size-3.5" /> NPS park page
                </a>
                <a
                    v-if="park.directions_url"
                    :href="park.directions_url"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1 text-emerald-700 hover:underline dark:text-emerald-400"
                >
                    <MapPin class="size-3.5" /> Directions
                </a>
            </div>
        </header>

        <p
            v-if="park.description"
            class="max-w-3xl text-sm leading-relaxed whitespace-pre-line text-muted-foreground"
        >
            {{ park.description }}
        </p>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <Card v-for="count in poiCounts" :key="count.value">
                <CardContent class="p-4">
                    <p class="text-2xl font-bold">{{ count.count }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ count.label }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <section class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Your visits</h2>
                <div class="flex gap-2">
                    <Button :disabled="checkingIn" @click="checkInNow">
                        <MapPin class="size-4" /> Check in now
                    </Button>
                    <Button
                        variant="outline"
                        @click="showPastForm = !showPastForm"
                    >
                        <CalendarPlus class="size-4" /> Log a past visit
                    </Button>
                </div>
            </div>

            <Card v-if="showPastForm">
                <CardContent class="p-5">
                    <form
                        class="flex flex-col gap-4"
                        @submit.prevent="submitPastVisit"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <Label for="started_at">Arrived</Label>
                                <Input
                                    id="started_at"
                                    v-model="pastForm.started_at"
                                    type="date"
                                    required
                                />
                                <p
                                    v-if="pastForm.errors.started_at"
                                    class="text-sm text-destructive"
                                >
                                    {{ pastForm.errors.started_at }}
                                </p>
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="ended_at"
                                    >Left
                                    <span class="text-muted-foreground"
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
                                    class="text-sm text-destructive"
                                >
                                    {{ pastForm.errors.ended_at }}
                                </p>
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="notes"
                                >Journal
                                <span class="text-muted-foreground"
                                    >(optional)</span
                                ></Label
                            >
                            <textarea
                                id="notes"
                                v-model="pastForm.notes"
                                rows="3"
                                placeholder="What stood out about this trip?"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                            />
                        </div>
                        <div class="flex gap-2">
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

            <div
                v-if="visits.length === 0"
                class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground"
            >
                You haven't logged a visit to this park yet.
            </div>

            <div v-else class="flex flex-col gap-2">
                <Card v-for="visit in visits" :key="visit.id">
                    <CardContent
                        class="flex items-center justify-between gap-3 p-4"
                    >
                        <Link
                            :href="visitShow(visit.id)"
                            class="flex flex-1 flex-col gap-0.5"
                        >
                            <span class="flex items-center gap-2 font-medium">
                                {{ visit.started_at }}
                                <span v-if="visit.ended_at">
                                    – {{ visit.ended_at }}</span
                                >
                                <span
                                    v-if="visit.is_live"
                                    class="rounded-full bg-emerald-700/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                >
                                    Live
                                </span>
                            </span>
                            <span class="text-xs text-muted-foreground">
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
                            <Trash2 class="size-4 text-destructive" />
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>
