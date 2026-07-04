<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Copy } from '@lucide/vue';
import { ref } from 'vue';
import Heading from '@/components/atoms/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { edit as profileEdit } from '@/routes/profile';
import {
    destroy as sharingDestroy,
    edit as sharingEdit,
    store as sharingStore,
} from '@/routes/sharing';

const props = defineProps<{
    shareUrl: string | null;
    isActive: boolean;
    shareEnabled: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Sharing settings', href: sharingEdit() }],
    },
});

const copied = ref(false);

const generate = (): void => {
    router.post(sharingStore().url, {}, { preserveScroll: true });
};

const revoke = (): void => {
    if (
        confirm(
            'Revoke this link? Anyone using the current URL will lose access.',
        )
    ) {
        router.delete(sharingDestroy().url, { preserveScroll: true });
    }
};

const copy = async (): Promise<void> => {
    if (!props.shareUrl) {
        return;
    }

    await navigator.clipboard.writeText(props.shareUrl);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};
</script>

<template>
    <Head title="Sharing settings" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Sharing"
            description="Share a read-only view of your parks list with a private link"
        />

        <div
            v-if="!shareEnabled"
            class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 text-sm"
        >
            Public sharing is currently off, so your link won't load for anyone.
            Turn on
            <Link :href="profileEdit()" class="font-medium underline"
                >public sharing in your profile</Link
            >
            to activate it.
        </div>

        <div v-if="isActive && shareUrl" class="flex flex-col gap-4">
            <div class="grid gap-1.5">
                <label class="text-sm font-medium" for="share-url"
                    >Your share link</label
                >
                <div class="flex gap-2">
                    <Input
                        id="share-url"
                        :model-value="shareUrl"
                        readonly
                        class="font-mono text-xs"
                        @focus="
                            (e: FocusEvent) =>
                                (e.target as HTMLInputElement).select()
                        "
                    />
                    <Button variant="outline" type="button" @click="copy">
                        <Check v-if="copied" class="size-4" />
                        <Copy v-else class="size-4" />
                        {{ copied ? 'Copied' : 'Copy' }}
                    </Button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Anyone with this link can see which parks you've visited and
                    your display name — nothing else.
                </p>
            </div>

            <div class="flex gap-2">
                <Button variant="outline" type="button" @click="generate">
                    Rotate link
                </Button>
                <Button variant="ghost" type="button" @click="revoke">
                    Revoke link
                </Button>
            </div>
        </div>

        <div v-else class="flex flex-col gap-3">
            <p class="text-sm text-muted-foreground">
                You don't have an active share link yet.
            </p>
            <div>
                <Button type="button" @click="generate">
                    Generate share link
                </Button>
            </div>
        </div>
    </div>
</template>
