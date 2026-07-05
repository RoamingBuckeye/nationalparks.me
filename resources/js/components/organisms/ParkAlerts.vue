<script setup lang="ts">
import { Ban, ChevronDown, Info, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import type { ParkAlert } from '@/types';

const props = defineProps<{ alerts: ParkAlert[] }>();

type Variant = {
    key: string;
    short: string;
    icon: Component;
};

const variants: Record<string, Variant> = {
    Danger: { key: 'danger', short: 'Danger', icon: TriangleAlert },
    'Park Closure': { key: 'closure', short: 'Closures', icon: Ban },
    Caution: { key: 'caution', short: 'Cautions', icon: TriangleAlert },
    Information: { key: 'info', short: 'Info', icon: Info },
};

const fallback: Variant = { key: 'notice', short: 'Notices', icon: Info };

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
    <section class="park-alerts">
        <template v-if="alerts.length === 0">
            <h2 class="park-alerts__title">Current alerts</h2>
            <p class="park-alerts__empty">
                No active alerts for this park right now.
            </p>
        </template>

        <Collapsible
            v-else
            v-slot="{ open }"
            :default-open="false"
            class="park-alerts__panel"
        >
            <CollapsibleTrigger class="park-alerts__summary">
                <div class="park-alerts__summary-info">
                    <span class="park-alerts__title">Current alerts</span>
                    <span class="park-alerts__count"
                        >({{ alerts.length }})</span
                    >
                    <span
                        v-for="entry in summary"
                        :key="entry.category"
                        class="park-alert-chip"
                        :class="`park-alert-chip--${entry.variant.key}`"
                    >
                        {{ entry.count }} {{ entry.variant.short }}
                    </span>
                </div>
                <ChevronDown
                    class="park-alerts__chevron"
                    :class="{ 'park-alerts__chevron--open': open }"
                />
            </CollapsibleTrigger>

            <CollapsibleContent>
                <div class="park-alerts__list">
                    <Collapsible
                        v-for="alert in decorated"
                        :key="alert.id"
                        v-slot="{ open: itemOpen }"
                        :default-open="false"
                        class="park-alert"
                        :class="`park-alert--${alert.variant.key}`"
                    >
                        <CollapsibleTrigger class="park-alert__trigger">
                            <component
                                :is="alert.variant.icon"
                                class="park-alert__icon"
                            />
                            <span class="park-alert__category">
                                {{ alert.category ?? 'Notice' }}
                            </span>
                            <span class="park-alert__title">
                                {{ alert.title }}
                            </span>
                            <ChevronDown
                                class="park-alert__chevron"
                                :class="{
                                    'park-alert__chevron--open': itemOpen,
                                }"
                            />
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            <div class="park-alert__body">
                                <p
                                    v-if="alert.description"
                                    class="park-alert__desc"
                                >
                                    {{ alert.description }}
                                </p>
                                <a
                                    v-if="alert.url"
                                    :href="alert.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="park-alert__link"
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
