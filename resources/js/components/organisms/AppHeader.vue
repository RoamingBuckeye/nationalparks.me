<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Map, Menu, Mountain, Stamp } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/atoms/AppLogo.vue';
import AppLogoIcon from '@/components/atoms/AppLogoIcon.vue';
import Breadcrumbs from '@/components/molecules/Breadcrumbs.vue';
import UserMenuContent from '@/components/molecules/UserMenuContent.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { dashboard, map as mapRoute, stamps as stampsRoute } from '@/routes';
import { index as parksIndex } from '@/routes/parks';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Parks', href: parksIndex(), icon: Mountain },
    { title: 'Map', href: mapRoute(), icon: Map },
    { title: 'Stamps', href: stampsRoute(), icon: Stamp },
];
</script>

<template>
    <div class="app-header">
        <div class="app-header__bar">
            <div class="app-header__inner">
                <!-- Mobile Menu -->
                <div class="app-header__mobile">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="app-header__menu-button"
                            >
                                <Menu class="app-header__menu-icon" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="app-header__sheet">
                            <SheetTitle class="sr-only"
                                >Navigation menu</SheetTitle
                            >
                            <SheetHeader class="app-header__sheet-header">
                                <AppLogoIcon class="app-header__sheet-logo" />
                            </SheetHeader>
                            <div class="app-header__mobile-nav-wrap">
                                <nav class="app-header__mobile-nav">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="app-header__mobile-link"
                                        :class="
                                            whenCurrentUrl(
                                                item.href,
                                                'app-header__mobile-link--active',
                                            )
                                        "
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="app-header__mobile-link-icon"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboard()" class="app-header__brand">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="app-header__desktop">
                    <NavigationMenu class="app-header__nav">
                        <NavigationMenuList class="app-header__nav-list">
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="app-header__nav-item"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        whenCurrentUrl(
                                            item.href,
                                            'app-header__nav-link--active',
                                        ),
                                        'app-header__nav-link',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="app-header__nav-icon"
                                    />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="isCurrentUrl(item.href)"
                                    class="app-header__nav-underline"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="app-header__actions">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="app-header__avatar-button"
                            >
                                <Avatar class="app-header__avatar">
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="app-header__avatar-fallback"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            align="end"
                            class="app-header__menu"
                        >
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="app-header__breadcrumbs"
        >
            <div class="app-header__breadcrumbs-inner">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
