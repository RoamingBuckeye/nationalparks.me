<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { KeyRound } from '@lucide/vue';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
import Heading from '@/components/atoms/Heading.vue';
import PasskeyItem from '@/components/molecules/PasskeyItem.vue';
import PasskeyRegister from '@/components/molecules/PasskeyRegister.vue';
import type { Passkey } from '@/types/auth';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

withDefaults(defineProps<Props>(), {
    canManagePasskeys: false,
    passkeys: () => [],
});

const handleDelete = (id: number, onError: () => void) => {
    router.delete(destroy.url(id), {
        preserveScroll: true,
        onError,
    });
};

const handleRegisterSuccess = () => {
    router.reload();
};
</script>

<template>
    <div v-if="canManagePasskeys" class="manage-passkeys">
        <Heading
            variant="small"
            title="Passkeys"
            description="Manage your passkeys for passwordless sign-in"
        />

        <div class="manage-passkeys__list">
            <template v-if="passkeys.length">
                <PasskeyItem
                    v-for="passkey in passkeys"
                    :key="passkey.id"
                    :passkey="passkey"
                    @remove="handleDelete"
                />
            </template>

            <div v-else class="manage-passkeys__empty">
                <div class="manage-passkeys__empty-icon-box">
                    <KeyRound class="manage-passkeys__empty-icon" />
                </div>
                <p class="manage-passkeys__empty-title">No passkeys yet</p>
                <p class="manage-passkeys__empty-text">
                    Add a passkey to sign in without a password
                </p>
            </div>
        </div>

        <PasskeyRegister @success="handleRegisterSuccess" />
    </div>
</template>
