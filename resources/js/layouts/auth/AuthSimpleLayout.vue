<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/atoms/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-simple">
        <div class="auth-simple__container">
            <div class="auth-simple__inner">
                <div class="auth-simple__brand">
                    <Link :href="home()" class="auth-simple__logo-link">
                        <div class="auth-simple__logo-box">
                            <AppLogoIcon class="auth-simple__logo-icon" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="auth-simple__heading">
                        <h1 class="auth-simple__title">{{ title }}</h1>
                        <p class="auth-simple__desc">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
