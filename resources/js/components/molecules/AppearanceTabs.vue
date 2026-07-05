<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div class="appearance-tabs">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            class="appearance-tabs__tab"
            :class="{ 'appearance-tabs__tab--active': appearance === value }"
        >
            <component :is="Icon" class="appearance-tabs__icon" />
            <span class="appearance-tabs__label">{{ label }}</span>
        </button>
    </div>
</template>
