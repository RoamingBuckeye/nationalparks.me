<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header class="heading" :class="{ 'heading--small': variant === 'small' }">
        <h2 class="heading__title">{{ title }}</h2>
        <p v-if="description" class="heading__description">
            {{ description }}
        </p>
    </header>
</template>
