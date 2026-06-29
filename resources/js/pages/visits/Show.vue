<script setup lang="ts">
import { Head, router, setLayoutProps, useForm } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as parksIndex, show as parkShow } from '@/routes/parks';
import {
    destroy as visitDestroy,
    show as visitShow,
    update as visitUpdate,
} from '@/routes/visits';
import { toggle as poiToggle } from '@/routes/visits/pois';

type PaginationLink = { url: string | null; label: string; active: boolean };

type Poi = { id: number; title: string; kind: string; kind_label: string };

const props = defineProps<{
    visit: {
        id: number;
        started_at: string;
        ended_at: string | null;
        is_live: boolean;
        notes: string | null;
    };
    park: { id: number; name: string };
    pois: { data: Poi[]; links: PaginationLink[] };
    checkedPoiIds: number[];
    totalPois: number;
    kinds: { value: string; label: string }[];
    filters: { kind: string; search: string };
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Parks', href: parksIndex() },
        { title: props.park.name, href: parkShow(props.park.id) },
        {
            title: `Visit · ${props.visit.started_at}`,
            href: visitShow(props.visit.id),
        },
    ],
});

const checkedSet = computed(() => new Set(props.checkedPoiIds));

const form = useForm({
    started_at: props.visit.started_at,
    ended_at: props.visit.ended_at ?? '',
    notes: props.visit.notes ?? '',
});

const saveVisit = (): void => {
    form.patch(visitUpdate(props.visit.id).url, { preserveScroll: true });
};

const endVisitToday = (): void => {
    form.ended_at = new Date().toISOString().slice(0, 10);
    saveVisit();
};

const togglePoi = (poiId: number): void => {
    router.post(
        poiToggle({ visit: props.visit.id, pointOfInterest: poiId }).url,
        {},
        { preserveScroll: true, preserveState: true },
    );
};

const deleteVisit = (): void => {
    if (
        confirm(
            'Delete this visit? This also removes its checked points of interest.',
        )
    ) {
        router.delete(visitDestroy(props.visit.id).url);
    }
};

// POI checklist filters.
const kind = ref(props.filters.kind);
const search = ref(props.filters.search);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilters = (): void => {
    router.get(
        visitShow(props.visit.id).url,
        { kind: kind.value, search: search.value },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

watch(kind, applyFilters);
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});
</script>

<template>
    <Head :title="`Visit · ${park.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ park.name }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ checkedPoiIds.length }} of {{ totalPois }} points of
                    interest checked off
                </p>
            </div>
            <Button variant="ghost" @click="deleteVisit">
                <Trash2 class="size-4 text-destructive" /> Delete visit
            </Button>
        </header>

        <Card>
            <CardContent class="p-5">
                <form class="flex flex-col gap-4" @submit.prevent="saveVisit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="started_at">Arrived</Label>
                            <Input
                                id="started_at"
                                v-model="form.started_at"
                                type="date"
                                required
                            />
                            <p
                                v-if="form.errors.started_at"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.started_at }}
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
                                v-model="form.ended_at"
                                type="date"
                            />
                            <p
                                v-if="form.errors.ended_at"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.ended_at }}
                            </p>
                        </div>
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="notes">Journal</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="What stood out about this trip?"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                        />
                    </div>
                    <div class="flex gap-2">
                        <Button type="submit" :disabled="form.processing">
                            Save
                        </Button>
                        <Button
                            v-if="visit.is_live"
                            type="button"
                            variant="outline"
                            @click="endVisitToday"
                        >
                            End visit today
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <section class="flex flex-col gap-4">
            <h2 class="text-lg font-semibold">Points of interest</h2>

            <div class="flex flex-col gap-3 sm:flex-row">
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search points of interest…"
                    class="sm:max-w-xs"
                />
                <select
                    v-model="kind"
                    class="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs"
                >
                    <option value="">All kinds</option>
                    <option v-for="k in kinds" :key="k.value" :value="k.value">
                        {{ k.label }}
                    </option>
                </select>
            </div>

            <div
                v-if="pois.data.length === 0"
                class="rounded-xl border border-dashed p-8 text-center text-sm text-muted-foreground"
            >
                No points of interest match your filters.
            </div>

            <ul v-else class="flex flex-col gap-1">
                <li
                    v-for="poi in pois.data"
                    :key="poi.id"
                    class="flex items-center gap-3 rounded-md border border-border/60 px-3 py-2"
                >
                    <Checkbox
                        :model-value="checkedSet.has(poi.id)"
                        @update:model-value="togglePoi(poi.id)"
                    />
                    <div class="flex flex-1 flex-col">
                        <span class="text-sm font-medium">{{ poi.title }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            poi.kind_label
                        }}</span>
                    </div>
                </li>
            </ul>

            <nav v-if="pois.links.length > 3" class="flex flex-wrap gap-1">
                <template v-for="(link, index) in pois.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md px-3 py-1.5 text-sm"
                        :class="
                            link.active
                                ? 'bg-emerald-700 text-white'
                                : 'text-muted-foreground hover:bg-muted'
                        "
                        @click.prevent="
                            router.get(
                                link.url,
                                {},
                                { preserveState: true, preserveScroll: true },
                            )
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="rounded-md px-3 py-1.5 text-sm text-muted-foreground opacity-50"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </section>
    </div>
</template>
