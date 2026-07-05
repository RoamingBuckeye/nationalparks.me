<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Check, Copy, ScanLine } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import InputError from '@/components/atoms/InputError.vue';
import AlertError from '@/components/molecules/AlertError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { useAppearance } from '@/composables/useAppearance';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { confirm } from '@/routes/two-factor';
import type { TwoFactorConfigContent } from '@/types';

type Props = {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
};

const { resolvedAppearance } = useAppearance();

const props = defineProps<Props>();
const isOpen = defineModel<boolean>('isOpen');

const { copy, copied } = useClipboard();
const { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } =
    useTwoFactorAuth();

const showVerificationStep = ref(false);
const code = ref<string>('');

const pinInputContainerRef = useTemplateRef('pinInputContainerRef');

const modalConfig = computed<TwoFactorConfigContent>(() => {
    if (props.twoFactorEnabled) {
        return {
            title: 'Two-factor authentication enabled',
            description:
                'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.',
            buttonText: 'Close',
        };
    }

    if (showVerificationStep.value) {
        return {
            title: 'Verify authentication code',
            description: 'Enter the 6-digit code from your authenticator app',
            buttonText: 'Continue',
        };
    }

    return {
        title: 'Enable two-factor authentication',
        description:
            'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app',
        buttonText: 'Continue',
    };
});

const handleModalNextStep = () => {
    if (props.requiresConfirmation) {
        showVerificationStep.value = true;

        nextTick(() => {
            pinInputContainerRef.value?.querySelector('input')?.focus();
        });

        return;
    }

    clearSetupData();
    isOpen.value = false;
};

const resetModalState = () => {
    if (props.twoFactorEnabled) {
        clearSetupData();
    }

    showVerificationStep.value = false;
    code.value = '';
};

watch(
    () => isOpen.value,
    async (isOpen) => {
        if (!isOpen) {
            resetModalState();

            return;
        }

        if (!qrCodeSvg.value) {
            await fetchSetupData();
        }
    },
);
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="two-factor-modal">
            <DialogHeader class="two-factor-modal__header">
                <div class="two-factor-modal__badge">
                    <div class="two-factor-modal__badge-inner">
                        <div class="two-factor-modal__grid-cols">
                            <div
                                v-for="i in 5"
                                :key="`col-${i}`"
                                class="two-factor-modal__grid-col"
                            />
                        </div>
                        <div class="two-factor-modal__grid-rows">
                            <div
                                v-for="i in 5"
                                :key="`row-${i}`"
                                class="two-factor-modal__grid-row"
                            />
                        </div>
                        <ScanLine class="two-factor-modal__scan-icon" />
                    </div>
                </div>
                <DialogTitle>{{ modalConfig.title }}</DialogTitle>
                <DialogDescription class="two-factor-modal__desc">
                    {{ modalConfig.description }}
                </DialogDescription>
            </DialogHeader>

            <div class="two-factor-modal__body">
                <template v-if="!showVerificationStep">
                    <AlertError v-if="errors?.length" :errors="errors" />
                    <template v-else>
                        <div class="two-factor-modal__qr-wrap">
                            <div class="two-factor-modal__qr">
                                <div
                                    v-if="!qrCodeSvg"
                                    class="two-factor-modal__qr-loading"
                                >
                                    <Spinner class="size-6" />
                                </div>
                                <div v-else class="two-factor-modal__qr-svg">
                                    <div
                                        v-html="qrCodeSvg"
                                        class="two-factor-modal__qr-image"
                                        :style="{
                                            filter:
                                                resolvedAppearance === 'dark'
                                                    ? 'invert(1) brightness(1.5)'
                                                    : undefined,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="two-factor-modal__actions">
                            <Button
                                class="two-factor-modal__button"
                                @click="handleModalNextStep"
                            >
                                {{ modalConfig.buttonText }}
                            </Button>
                        </div>

                        <div class="two-factor-modal__divider">
                            <div class="two-factor-modal__divider-line" />
                            <span class="two-factor-modal__divider-text"
                                >or, enter the code manually</span
                            >
                        </div>

                        <div class="two-factor-modal__manual">
                            <div class="two-factor-modal__key">
                                <div
                                    v-if="!manualSetupKey"
                                    class="two-factor-modal__key-loading"
                                >
                                    <Spinner />
                                </div>
                                <template v-else>
                                    <input
                                        type="text"
                                        readonly
                                        :value="manualSetupKey"
                                        class="two-factor-modal__key-input"
                                    />
                                    <button
                                        @click="copy(manualSetupKey || '')"
                                        class="two-factor-modal__key-copy"
                                    >
                                        <Check
                                            v-if="copied"
                                            class="two-factor-modal__copy-icon two-factor-modal__copy-icon--done"
                                        />
                                        <Copy
                                            v-else
                                            class="two-factor-modal__copy-icon"
                                        />
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </template>

                <template v-else>
                    <Form
                        v-bind="confirm.form()"
                        error-bag="confirmTwoFactorAuthentication"
                        reset-on-error
                        @finish="code = ''"
                        @success="isOpen = false"
                        v-slot="{ errors, processing }"
                    >
                        <input type="hidden" name="code" :value="code" />
                        <div
                            ref="pinInputContainerRef"
                            class="two-factor-modal__pin"
                        >
                            <div class="two-factor-modal__pin-inner">
                                <InputOTP
                                    id="otp"
                                    v-model="code"
                                    :maxlength="6"
                                    :disabled="processing"
                                    autofocus
                                >
                                    <InputOTPGroup>
                                        <InputOTPSlot
                                            v-for="index in 6"
                                            :key="index"
                                            :index="index - 1"
                                        />
                                    </InputOTPGroup>
                                </InputOTP>
                                <InputError :message="errors?.code" />
                            </div>

                            <div class="two-factor-modal__actions">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="two-factor-modal__button-flex"
                                    @click="showVerificationStep = false"
                                    :disabled="processing"
                                >
                                    Back
                                </Button>
                                <Button
                                    type="submit"
                                    class="two-factor-modal__button-flex"
                                    :disabled="processing || code.length < 6"
                                >
                                    Confirm
                                </Button>
                            </div>
                        </div>
                    </Form>
                </template>
            </div>
        </DialogContent>
    </Dialog>
</template>
