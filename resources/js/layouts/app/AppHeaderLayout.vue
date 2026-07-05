<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppHeader from '@/components/organisms/AppHeader.vue';
import StampReveal from '@/components/organisms/StampReveal.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header">
            <slot />
        </AppContent>
        <Toaster />
        <StampReveal />
    </AppShell>
</template>
