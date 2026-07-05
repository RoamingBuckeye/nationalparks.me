<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import Stamp from '@/components/molecules/Stamp.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { EarnedStamp, Stamp as StampType } from '@/types/stamps';

const isOpen = ref(false);
const earned = ref<StampType[]>([]);

// The flash payload is the earned subset; present each as a full, earned stamp.
function toStamp(stamp: EarnedStamp): StampType {
    return {
        ...stamp,
        category: null,
        earned: true,
        progress: 0,
        required: 0,
        earned_at: null,
        vintage_year: null,
    };
}

let stopListening: (() => void) | undefined;

onMounted(() => {
    stopListening = router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const stamps = flash?.stampsEarned as EarnedStamp[] | undefined;

        if (!stamps || stamps.length === 0) {
            return;
        }

        earned.value = stamps.map(toStamp);
        isOpen.value = true;
    });
});

onUnmounted(() => stopListening?.());
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="stamp-reveal">
            <DialogHeader class="stamp-reveal__header">
                <DialogTitle>
                    {{
                        earned.length > 1
                            ? 'New stamps earned!'
                            : 'New stamp earned!'
                    }}
                </DialogTitle>
                <DialogDescription>
                    You added
                    {{
                        earned.length > 1
                            ? `${earned.length} stamps`
                            : 'a stamp'
                    }}
                    to your collection.
                </DialogDescription>
            </DialogHeader>

            <div class="stamp-reveal__stamps">
                <Stamp v-for="stamp in earned" :key="stamp.id" :stamp="stamp" />
            </div>

            <DialogFooter class="stamp-reveal__footer">
                <Button @click="isOpen = false">Nice!</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
