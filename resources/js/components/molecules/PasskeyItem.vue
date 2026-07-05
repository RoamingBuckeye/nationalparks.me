<script setup lang="ts">
import { KeyRound, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Passkey } from '@/types/auth';

const props = defineProps<{
    passkey: Passkey;
}>();

const emit = defineEmits<{
    remove: [id: number, onError: () => void];
}>();

const isDeleting = ref(false);

const handleDelete = () => {
    isDeleting.value = true;
    emit('remove', props.passkey.id, () => {
        isDeleting.value = false;
    });
};
</script>

<template>
    <div class="passkey-item">
        <div class="passkey-item__info">
            <div class="passkey-item__icon-box">
                <KeyRound class="passkey-item__icon" />
            </div>
            <div class="passkey-item__details">
                <div class="passkey-item__name-row">
                    <p class="passkey-item__name">{{ passkey.name }}</p>
                    <span
                        v-if="passkey.authenticator"
                        class="passkey-item__badge"
                    >
                        {{ passkey.authenticator }}
                    </span>
                </div>
                <p class="passkey-item__meta">
                    Added {{ passkey.created_at_diff }}
                    <template v-if="passkey.last_used_at_diff">
                        <span class="passkey-item__meta-sep">/</span>
                        Last used {{ passkey.last_used_at_diff }}
                    </template>
                </p>
            </div>
        </div>

        <Dialog>
            <DialogTrigger as-child>
                <Button variant="ghost" size="sm" class="passkey-item__remove">
                    <Trash2 class="passkey-item__remove-icon" />
                    <span class="sr-only">Remove</span>
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogTitle>Remove passkey</DialogTitle>
                <DialogDescription>
                    Are you sure you want to remove the "{{ passkey.name }}"
                    passkey? You will no longer be able to use it to sign in.
                </DialogDescription>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        :disabled="isDeleting"
                        @click="handleDelete"
                    >
                        {{ isDeleting ? 'Removing...' : 'Remove passkey' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
