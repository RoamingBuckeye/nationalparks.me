<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/atoms/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { edit as editSharing } from '@/routes/sharing';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Security',
        href: editSecurity(),
    },
    {
        title: 'Sharing',
        href: editSharing(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="settings-layout">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="settings-layout__body">
            <aside class="settings-layout__aside">
                <nav class="settings-layout__nav" aria-label="Settings">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'settings-layout__nav-btn',
                            {
                                'settings-layout__nav-btn--active':
                                    isCurrentOrParentUrl(item.href),
                            },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component
                                :is="item.icon"
                                class="settings-layout__nav-icon"
                            />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="settings-layout__separator" />

            <div class="settings-layout__content">
                <section class="settings-layout__section">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
