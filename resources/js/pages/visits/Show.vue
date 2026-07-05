<script setup lang="ts">
import { Head, router, setLayoutProps, useForm } from '@inertiajs/vue3';
import { ImagePlus, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as parksIndex, show as parkShow } from '@/routes/parks';
import { destroy as photoDestroy } from '@/routes/photos';
import {
    destroy as visitDestroy,
    show as visitShow,
    update as visitUpdate,
} from '@/routes/visits';
import { store as photosStore } from '@/routes/visits/photos';
import { toggle as poiToggle } from '@/routes/visits/pois';

type PaginationLink = { url: string | null; label: string; active: boolean };

type Poi = { id: number; title: string; kind: string; kind_label: string };

type VisitPhoto = {
    id: number;
    url: string;
    thumbnail_url: string;
    original_filename: string;
    taken_at: string | null;
};

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
    photos: VisitPhoto[];
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

// Photos.
const photoForm = useForm<{ photos: File[] }>({ photos: [] });
const fileInput = ref<HTMLInputElement | null>(null);

const uploadPhotos = (event: Event): void => {
    const files = (event.target as HTMLInputElement).files;

    if (!files || files.length === 0) {
        return;
    }

    photoForm.photos = Array.from(files);
    photoForm.post(photosStore(props.visit.id).url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            photoForm.reset();

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const deletePhoto = (id: number): void => {
    if (confirm('Delete this photo?')) {
        router.delete(photoDestroy(id).url, { preserveScroll: true });
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

    <div class="page page--gap-6">
        <header class="visit-header">
            <div>
                <h1 class="page-title">
                    {{ park.name }}
                </h1>
                <p class="page-desc">
                    {{ checkedPoiIds.length }} of {{ totalPois }} points of
                    interest checked off
                </p>
            </div>
            <Button variant="ghost" @click="deleteVisit">
                <Trash2 class="visit-delete-icon" /> Delete visit
            </Button>
        </header>

        <Card>
            <CardContent class="visit-form-body">
                <form class="visit-form" @submit.prevent="saveVisit">
                    <div class="visit-form-grid">
                        <div class="visit-form-field">
                            <Label for="started_at">Arrived</Label>
                            <Input
                                id="started_at"
                                v-model="form.started_at"
                                type="date"
                                required
                            />
                            <p
                                v-if="form.errors.started_at"
                                class="visit-form-error"
                            >
                                {{ form.errors.started_at }}
                            </p>
                        </div>
                        <div class="visit-form-field">
                            <Label for="ended_at"
                                >Left
                                <span class="visit-optional"
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
                                class="visit-form-error"
                            >
                                {{ form.errors.ended_at }}
                            </p>
                        </div>
                    </div>
                    <div class="visit-form-field">
                        <Label for="notes">Journal</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="What stood out about this trip?"
                            class="visit-textarea"
                        />
                    </div>
                    <div class="visit-form-actions">
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

        <section class="visit-section">
            <div class="page-topbar">
                <h2 class="visit-section-title">Photos</h2>
                <label>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        class="sr-only"
                        @change="uploadPhotos"
                    />
                    <span
                        class="visit-upload-btn"
                        :class="{
                            'visit-upload-btn--disabled': photoForm.processing,
                        }"
                    >
                        <ImagePlus />
                        {{ photoForm.processing ? 'Uploading…' : 'Add photos' }}
                    </span>
                </label>
            </div>

            <p v-if="photoForm.errors.photos" class="visit-form-error">
                {{ photoForm.errors.photos }}
            </p>

            <div v-if="photos.length === 0" class="visit-empty">
                No photos yet. Add shots from this trip — we'll read the date
                and location from each image when available.
            </div>

            <div v-else class="visit-photo-grid">
                <div
                    v-for="photo in photos"
                    :key="photo.id"
                    class="visit-photo"
                >
                    <a :href="photo.url" target="_blank" rel="noopener">
                        <img
                            :src="photo.thumbnail_url"
                            :alt="photo.original_filename"
                            loading="lazy"
                            class="visit-photo-img"
                        />
                    </a>
                    <span v-if="photo.taken_at" class="visit-photo-date">
                        {{ photo.taken_at }}
                    </span>
                    <Button
                        variant="secondary"
                        size="icon"
                        class="visit-photo-delete"
                        aria-label="Delete photo"
                        @click="deletePhoto(photo.id)"
                    >
                        <Trash2 class="visit-delete-icon" />
                    </Button>
                </div>
            </div>
        </section>

        <section class="visit-section">
            <h2 class="visit-section-title">Points of interest</h2>

            <div class="visit-filters">
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search points of interest…"
                    aria-label="Search points of interest"
                    class="visit-search"
                />
                <select
                    v-model="kind"
                    aria-label="Filter by kind"
                    class="visit-select"
                >
                    <option value="">All kinds</option>
                    <option v-for="k in kinds" :key="k.value" :value="k.value">
                        {{ k.label }}
                    </option>
                </select>
            </div>

            <div v-if="pois.data.length === 0" class="visit-empty">
                No points of interest match your filters.
            </div>

            <ul v-else class="visit-poi-list">
                <li v-for="poi in pois.data" :key="poi.id" class="visit-poi">
                    <Checkbox
                        :model-value="checkedSet.has(poi.id)"
                        @update:model-value="togglePoi(poi.id)"
                    />
                    <div class="visit-poi-text">
                        <span class="visit-poi-title">{{ poi.title }}</span>
                        <span class="visit-poi-kind">{{ poi.kind_label }}</span>
                    </div>
                </li>
            </ul>

            <nav v-if="pois.links.length > 3" class="visit-pagination">
                <template v-for="(link, index) in pois.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        class="visit-page-link"
                        :class="{ 'visit-page-link--active': link.active }"
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
                        class="visit-page-link visit-page-link--disabled"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </section>
    </div>
</template>
