<script setup lang="ts">
import { Check, Lock } from '@lucide/vue';
import { computed, useId } from 'vue';
import type { Stamp } from '@/types/stamps';

const props = withDefaults(
    defineProps<{
        stamp: Stamp;
        size?: number;
        showLabel?: boolean;
    }>(),
    {
        size: 128,
        showLabel: true,
    },
);

// Squircle "stamp" silhouette, from the Gowalla reference badges.
const SHELL =
    'M64 0C28.8 0 0 28.8 0 64v40c0 13.2 10.8 24 24 24h80c13.2 0 24-10.8 24-24V64c0-35.2-28.8-64-64-64';
const RING =
    'M24 117c-7.2 0-13-5.8-13-13V64c0-29.2 23.8-53 53-53s53 23.8 53 53v40c0 7.2-5.8 13-13 13z';
const SCENE =
    'M64 14c27.6 0 50 22.4 50 50v40c0 5.5-4.5 10-10 10H24c-5.5 0-10-4.5-10-10V64c0-27.6 22.4-50 50-50';

const clipId = `stamp-scene-${useId()}`;

const accent = computed(() => props.stamp.accent_color ?? '#6b7280');
const isEarned = computed(() => props.stamp.earned);

const ariaLabel = computed(() => {
    const { name, earned, vintage_year, progress, required } = props.stamp;

    if (earned) {
        return vintage_year
            ? `${name} stamp, earned in ${vintage_year}`
            : `${name} stamp, earned`;
    }

    return `${name} stamp, locked — ${progress} of ${required} parks visited`;
});
</script>

<template>
    <div class="inline-flex flex-col items-center gap-2 text-center">
        <div
            class="relative"
            :style="{ width: `${size}px`, height: `${size}px` }"
        >
            <svg
                viewBox="0 0 128 128"
                class="h-full w-full transition duration-300"
                :class="{ 'opacity-60 grayscale': !isEarned }"
                role="img"
                :aria-label="ariaLabel"
            >
                <defs>
                    <clipPath :id="clipId">
                        <path :d="SCENE" />
                    </clipPath>
                </defs>

                <path :d="SHELL" fill="#fff" stroke="#e5e7eb" />
                <path :d="RING" :fill="accent" />
                <path :d="SCENE" fill="#eef3f8" />

                <!-- Placeholder scene (a park motif) until bespoke art lands. -->
                <g :clip-path="`url(#${clipId})`">
                    <circle cx="90" cy="45" r="11" fill="#fce2a0" />
                    <path
                        d="M12 106 L42 62 L70 106 Z"
                        :fill="accent"
                        fill-opacity="0.85"
                    />
                    <path d="M50 106 L84 54 L116 106 Z" :fill="accent" />
                </g>
            </svg>

            <span
                v-if="isEarned"
                class="absolute right-0 bottom-1 flex size-6 items-center justify-center rounded-full bg-brand-700 text-white ring-2 ring-white"
                aria-hidden="true"
            >
                <Check class="size-3.5" />
            </span>
            <span
                v-else
                class="absolute right-0 bottom-1 flex size-6 items-center justify-center rounded-full bg-gray-400 text-white ring-2 ring-white"
                aria-hidden="true"
            >
                <Lock class="size-3" />
            </span>
        </div>

        <div v-if="showLabel" class="max-w-32">
            <p class="text-sm leading-tight font-semibold">{{ stamp.name }}</p>
            <p class="text-xs text-muted-foreground">
                <template v-if="isEarned && stamp.vintage_year">
                    Earned · {{ stamp.vintage_year }}
                </template>
                <template v-else-if="isEarned">Earned</template>
                <template v-else>
                    <span class="font-mono tabular-nums"
                        >{{ stamp.progress }}/{{ stamp.required }}</span
                    >
                </template>
            </p>
        </div>
    </div>
</template>
