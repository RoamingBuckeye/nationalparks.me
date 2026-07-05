<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/atoms/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-split">
        <div class="auth-split__panel">
            <div class="auth-split__panel-bg" />
            <Link :href="home()" class="auth-split__brand">
                <AppLogoIcon class="auth-split__logo" />
                {{ name }}
            </Link>
        </div>
        <div class="auth-split__main">
            <div class="auth-split__form">
                <div class="auth-split__heading">
                    <h1 class="auth-split__title" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="auth-split__desc" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
