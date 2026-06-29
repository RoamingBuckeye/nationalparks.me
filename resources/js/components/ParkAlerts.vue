<script setup lang="ts">
import { Ban, ChevronDown, Info, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';

type Alert = {
    id: number;
    category: string | null;
    severity: number;
    title: string;
    description: string | null;
    url: string | null;
};

const props = defineProps<{ alerts: Alert[] }>();

type Variant = {
    wrap: string;
    accent: string;
    chip: string;
    short: string;
    icon: Component;
};

const variants: Record<string, Variant> = {
    Danger: {
        wrap: 'border-red-500/30 bg-red-500/10',
        accent: 'text-red-600 dark:text-red-400',
        chip: 'bg-red-500/15 text-red-700 dark:text-red-400',
        short: 'Danger',
        icon: TriangleAlert,
    },
    'Park Closure': {
        wrap: 'border-orange-500/30 bg-orange-500/10',
        accent: 'text-orange-600 dark:text-orange-400',
        chip: 'bg-orange-500/15 text-orange-700 dark:text-orange-400',
        short: 'Closures',
        icon: Ban,
    },
    Caution: {
        wrap: 'border-amber-500/30 bg-amber-500/10',
        accent: 'text-amber-600 dark:text-amber-500',
        chip: 'bg-amber-500/15 text-amber-700 dark:text-amber-500',
        short: 'Cautions',
        icon: TriangleAlert,
    },
    Information: {
        wrap: 'border-blue-500/30 bg-blue-500/10',
        accent: 'text-blue-600 dark:text-blue-400',
        chip: 'bg-blue-500/15 text-blue-700 dark:text-blue-400',
        short: 'Info',
        icon: Info,
    },
};

const fallback: Variant = {
    wrap: 'border-border bg-muted/40',
    accent: 'text-muted-foreground',
    chip: 'bg-muted text-muted-foreground',
    short: 'Notices',
    icon: Info,
};

const variantFor = (category: string | null): Variant =>
    (category && variants[category]) || fallback;

const decorated = computed(() =>
    props.alerts.map((alert) => ({
        ...alert,
        variant: variantFor(alert.category),
    })),
);

// Severity-ordered count chips for the collapsed header.
const summary = computed(() =>
    ['Danger', 'Park Closure', 'Caution', 'Information']
        .map((category) => ({
            category,
            count: props.alerts.filter((alert) => alert.category === category)
                .length,
            variant: variants[category],
        }))
        .filter((entry) => entry.count > 0),
);
</script>

<template>
    <section class="flex flex-col gap-2">
        <template v-if="alerts.length === 0">
            <h2 class="text-lg font-semibold">Current alerts</h2>
            <p class="text-sm text-muted-foreground">
                No active alerts for this park right now.
            </p>
        </template>

        <Collapsible
            v-else
            v-slot="{ open }"
            :default-open="false"
            class="overflow-hidden rounded-xl border"
        >
            <CollapsibleTrigger
                class="flex w-full items-center justify-between gap-3 p-4 text-left transition-colors hover:bg-muted/50"
            >
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-lg font-semibold">Current alerts</span>
                    <span class="text-sm text-muted-foreground"
                        >({{ alerts.length }})</span
                    >
                    <span
                        v-for="entry in summary"
                        :key="entry.category"
                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="entry.variant.chip"
                    >
                        {{ entry.count }} {{ entry.variant.short }}
                    </span>
                </div>
                <ChevronDown
                    class="size-5 shrink-0 text-muted-foreground transition-transform"
                    :class="open ? 'rotate-180' : ''"
                />
            </CollapsibleTrigger>

            <CollapsibleContent>
                <div class="flex flex-col gap-1.5 border-t p-3">
                    <Collapsible
                        v-for="alert in decorated"
                        :key="alert.id"
                        v-slot="{ open: itemOpen }"
                        :default-open="false"
                        class="overflow-hidden rounded-lg border"
                        :class="alert.variant.wrap"
                    >
                        <CollapsibleTrigger
                            class="flex w-full items-center gap-3 p-3 text-left"
                        >
                            <component
                                :is="alert.variant.icon"
                                class="size-4 shrink-0"
                                :class="alert.variant.accent"
                            />
                            <span
                                class="shrink-0 text-xs font-medium tracking-wide uppercase"
                                :class="alert.variant.accent"
                            >
                                {{ alert.category ?? 'Notice' }}
                            </span>
                            <span class="flex-1 truncate text-sm font-medium">
                                {{ alert.title }}
                            </span>
                            <ChevronDown
                                class="size-4 shrink-0 text-muted-foreground transition-transform"
                                :class="itemOpen ? 'rotate-180' : ''"
                            />
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            <div
                                class="flex flex-col gap-1 border-t px-3 py-2 sm:pl-10"
                            >
                                <p
                                    v-if="alert.description"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ alert.description }}
                                </p>
                                <a
                                    v-if="alert.url"
                                    :href="alert.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-400"
                                >
                                    Details →
                                </a>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </section>
</template>
