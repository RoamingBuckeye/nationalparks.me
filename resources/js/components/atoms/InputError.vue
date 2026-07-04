<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="input-error">
            {{ message }}
        </p>
    </div>
</template>
