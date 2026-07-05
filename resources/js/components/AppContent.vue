<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    class?: string;
}>();

const className = computed(() => props.class);
</script>

<template>
    <main class="app-content" :class="className">
        <slot />
    </main>
</template>
