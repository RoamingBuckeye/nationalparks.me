<script setup lang="ts">
import { Form, Head, router, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watchEffect } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { emailChallenge, emailCode } from '@/routes/two-factor';
import { store } from '@/routes/two-factor/login';
import type { TwoFactorConfigContent } from '@/types';

defineProps<{
    maskedEmail: string | null;
}>();

type ChallengeMode = 'totp' | 'recovery' | 'email';

const mode = ref<ChallengeMode>('totp');
const code = ref<string>('');
const emailCodeValue = ref<string>('');
const emailCodeSent = ref<boolean>(false);
const sendingEmailCode = ref<boolean>(false);

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (mode.value === 'recovery') {
        return {
            title: 'Recovery code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
            buttonText: 'login using an authentication code',
        };
    }

    if (mode.value === 'email') {
        return {
            title: 'Email code',
            description:
                'We can email a one-time code to confirm access to your account.',
            buttonText: 'login using an authentication code',
        };
    }

    return {
        title: 'Authentication code',
        description:
            'Enter the authentication code provided by your authenticator application.',
        buttonText: 'login using a recovery code',
    };
});

watchEffect(() => {
    setLayoutProps({
        title: authConfigContent.value.title,
        description: authConfigContent.value.description,
    });
});

const switchMode = (next: ChallengeMode, clearErrors?: () => void): void => {
    mode.value = next;
    clearErrors?.();
    code.value = '';
    emailCodeValue.value = '';
    emailCodeSent.value = false;
};

const sendEmailCode = (): void => {
    router.post(
        emailCode.url(),
        {},
        {
            preserveScroll: true,
            onStart: () => (sendingEmailCode.value = true),
            onFinish: () => (sendingEmailCode.value = false),
            onSuccess: () => (emailCodeSent.value = true),
        },
    );
};
</script>

<template>
    <Head title="Two-factor authentication" />

    <div class="space-y-6">
        <template v-if="mode === 'totp'">
            <Form
                v-bind="store.form()"
                class="space-y-4"
                reset-on-error
                @error="code = ''"
                #default="{ errors, processing, clearErrors }"
            >
                <input type="hidden" name="code" :value="code" />
                <div
                    class="flex flex-col items-center justify-center space-y-3 text-center"
                >
                    <div class="flex w-full items-center justify-center">
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
                    </div>
                    <InputError :message="errors.code" />
                </div>
                <Button type="submit" class="w-full" :disabled="processing"
                    >Continue</Button
                >
                <div
                    class="space-y-1 text-center text-sm text-muted-foreground"
                >
                    <div>
                        <span>or you can </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => switchMode('recovery', clearErrors)"
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                    <div>
                        <span>or </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => switchMode('email', clearErrors)"
                        >
                            login using an email code
                        </button>
                    </div>
                </div>
            </Form>
        </template>

        <template v-else-if="mode === 'recovery'">
            <Form
                v-bind="store.form()"
                class="space-y-4"
                reset-on-error
                #default="{ errors, processing, clearErrors }"
            >
                <Input
                    name="recovery_code"
                    type="text"
                    placeholder="Enter recovery code"
                    autofocus
                    required
                />
                <InputError :message="errors.recovery_code" />
                <Button type="submit" class="w-full" :disabled="processing"
                    >Continue</Button
                >

                <div class="text-center text-sm text-muted-foreground">
                    <span>or you can </span>
                    <button
                        type="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        @click="() => switchMode('totp', clearErrors)"
                    >
                        {{ authConfigContent.buttonText }}
                    </button>
                </div>
            </Form>
        </template>

        <template v-else>
            <div class="space-y-4">
                <Button
                    type="button"
                    variant="outline"
                    class="w-full"
                    :disabled="sendingEmailCode"
                    @click="sendEmailCode"
                >
                    {{
                        emailCodeSent
                            ? 'Resend code'
                            : maskedEmail
                              ? `Email a code to ${maskedEmail}`
                              : 'Email me a code'
                    }}
                </Button>

                <Form
                    v-if="emailCodeSent"
                    v-bind="emailChallenge.form()"
                    class="space-y-4"
                    reset-on-error
                    @error="emailCodeValue = ''"
                    #default="{ errors, processing }"
                >
                    <input type="hidden" name="code" :value="emailCodeValue" />
                    <div
                        class="flex flex-col items-center justify-center space-y-3 text-center"
                    >
                        <div class="flex w-full items-center justify-center">
                            <InputOTP
                                id="email-otp"
                                v-model="emailCodeValue"
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
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <Button type="submit" class="w-full" :disabled="processing"
                        >Continue</Button
                    >
                </Form>

                <div class="text-center text-sm text-muted-foreground">
                    <span>or you can </span>
                    <button
                        type="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        @click="() => switchMode('totp')"
                    >
                        {{ authConfigContent.buttonText }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
