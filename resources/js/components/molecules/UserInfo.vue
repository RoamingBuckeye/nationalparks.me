<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <Avatar class="user-info__avatar">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
        <AvatarFallback class="user-info__fallback">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="user-info__text">
        <span class="user-info__name">{{ user.name }}</span>
        <span v-if="showEmail" class="user-info__email">{{ user.email }}</span>
    </div>
</template>
