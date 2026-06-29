<script setup lang="ts">
import { Ban, Info, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';

type Alert = {
    id: number;
    category: string | null;
    severity: number;
    title: string;
    description: string | null;
    url: string | null;
};

const props = defineProps<{ alerts: Alert[] }>();

type Variant = { wrap: string; accent: string; icon: Component };

const variants: Record<string, Variant> = {
    Danger: {
        wrap: 'border-red-500/30 bg-red-500/10',
        accent: 'text-red-600 dark:text-red-400',
        icon: TriangleAlert,
    },
    'Park Closure': {
        wrap: 'border-orange-500/30 bg-orange-500/10',
        accent: 'text-orange-600 dark:text-orange-400',
        icon: Ban,
    },
    Caution: {
        wrap: 'border-amber-500/30 bg-amber-500/10',
        accent: 'text-amber-600 dark:text-amber-500',
        icon: TriangleAlert,
    },
    Information: {
        wrap: 'border-blue-500/30 bg-blue-500/10',
        accent: 'text-blue-600 dark:text-blue-400',
        icon: Info,
    },
};

const fallback: Variant = {
    wrap: 'border-border bg-muted/40',
    accent: 'text-muted-foreground',
    icon: Info,
};

const decorated = computed(() =>
    props.alerts.map((alert) => ({
        ...alert,
        variant: (alert.category && variants[alert.category]) || fallback,
    })),
);
</script>

<template>
    <section class="flex flex-col gap-3">
        <h2 class="text-lg font-semibold">
            Current alerts
            <span
                v-if="alerts.length"
                class="text-sm font-normal text-muted-foreground"
            >
                ({{ alerts.length }})
            </span>
        </h2>

        <p v-if="alerts.length === 0" class="text-sm text-muted-foreground">
            No active alerts for this park right now.
        </p>

        <div v-else class="flex flex-col gap-2">
            <div
                v-for="alert in decorated"
                :key="alert.id"
                class="rounded-lg border p-4"
                :class="alert.variant.wrap"
            >
                <div class="flex gap-3">
                    <component
                        :is="alert.variant.icon"
                        class="mt-0.5 size-5 shrink-0"
                        :class="alert.variant.accent"
                    />
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-xs font-medium tracking-wide uppercase"
                            :class="alert.variant.accent"
                        >
                            {{ alert.category ?? 'Notice' }}
                        </span>
                        <p class="font-medium">{{ alert.title }}</p>
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
                </div>
            </div>
        </div>
    </section>
</template>
