<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole, RefreshCw } from '@lucide/vue';
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';
import AlertError from '@/components/molecules/AlertError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { regenerateRecoveryCodes } from '@/routes/two-factor';

const { recoveryCodesList, fetchRecoveryCodes, errors } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = useTemplateRef('recoveryCodeSectionRef');

const toggleRecoveryCodesVisibility = async () => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        recoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
});
</script>

<template>
    <Card class="recovery-codes">
        <CardHeader>
            <CardTitle class="recovery-codes__title">
                <LockKeyhole />2FA recovery codes
            </CardTitle>
            <CardDescription>
                Recovery codes let you regain access if you lose your 2FA
                device. Store them in a secure password manager.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div class="recovery-codes__actions">
                <Button
                    @click="toggleRecoveryCodesVisibility"
                    class="recovery-codes__view-btn"
                >
                    <component :is="isRecoveryCodesVisible ? EyeOff : Eye" />
                    {{ isRecoveryCodesVisible ? 'Hide' : 'View' }} recovery
                    codes
                </Button>

                <Form
                    v-if="isRecoveryCodesVisible && recoveryCodesList.length"
                    v-bind="regenerateRecoveryCodes.form()"
                    method="post"
                    :options="{ preserveScroll: true }"
                    @success="fetchRecoveryCodes"
                    #default="{ processing }"
                >
                    <Button
                        variant="secondary"
                        type="submit"
                        :disabled="processing"
                    >
                        <RefreshCw /> Regenerate codes
                    </Button>
                </Form>
            </div>
            <div
                :class="[
                    'recovery-codes__reveal',
                    { 'recovery-codes__reveal--open': isRecoveryCodesVisible },
                ]"
            >
                <div v-if="errors?.length" class="recovery-codes__error">
                    <AlertError :errors="errors" />
                </div>
                <div v-else class="recovery-codes__body">
                    <div
                        ref="recoveryCodeSectionRef"
                        class="recovery-codes__list"
                    >
                        <div
                            v-if="!recoveryCodesList.length"
                            class="recovery-codes__skeletons"
                        >
                            <div
                                v-for="n in 8"
                                :key="n"
                                class="recovery-codes__skeleton"
                            ></div>
                        </div>
                        <div
                            v-else
                            v-for="(code, index) in recoveryCodesList"
                            :key="index"
                        >
                            {{ code }}
                        </div>
                    </div>
                    <p class="recovery-codes__hint">
                        Each recovery code can be used once to access your
                        account and will be removed after use. If you need more,
                        click
                        <span class="recovery-codes__hint-strong"
                            >Regenerate codes</span
                        >
                        above.
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
