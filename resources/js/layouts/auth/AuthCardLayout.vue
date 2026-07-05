<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/atoms/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-card">
        <div class="auth-card__container">
            <Link :href="home()" class="auth-card__logo-link">
                <div class="auth-card__logo-box">
                    <AppLogoIcon class="auth-card__logo-icon" />
                </div>
            </Link>

            <div class="auth-card__card-wrap">
                <Card>
                    <CardHeader class="auth-card__header">
                        <CardTitle class="auth-card__title">{{
                            title
                        }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="auth-card__content">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
