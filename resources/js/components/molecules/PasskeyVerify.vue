<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { usePasskeyVerify } from '@laravel/passkeys/vue';
import { KeyRound } from '@lucide/vue';
import InputError from '@/components/atoms/InputError.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

const props = defineProps<Props>();

const { verify, isLoading, error, isSupported } = usePasskeyVerify({
    ...(props.routes
        ? {
              routes: {
                  options: props.routes.options.url,
                  submit: props.routes.submit.url,
              },
          }
        : {}),
    onSuccess: (response) => {
        router.visit(response.redirect ?? '/dashboard');
    },
});
</script>

<template>
    <div v-if="isSupported">
        <div class="passkey-verify__actions">
            <Button
                type="button"
                variant="outline"
                class="passkey-verify__button"
                @click="verify"
                :disabled="isLoading"
            >
                <Spinner v-if="isLoading" />
                <KeyRound v-else />
                {{
                    isLoading
                        ? (props.loadingLabel ?? 'Authenticating...')
                        : (props.label ?? 'Sign in with a passkey')
                }}
            </Button>

            <div v-if="error" class="passkey-verify__error">
                <InputError :message="error" />
            </div>
        </div>

        <div class="passkey-verify__separator">
            <div class="passkey-verify__separator-line">
                <Separator />
            </div>
            <div class="passkey-verify__separator-label">
                <span class="passkey-verify__separator-text">
                    {{ props.separator ?? 'Or continue with email' }}
                </span>
            </div>
        </div>
    </div>
</template>
