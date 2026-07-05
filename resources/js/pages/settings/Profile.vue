<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/atoms/Heading.vue';
import InputError from '@/components/atoms/InputError.vue';
import DeleteUser from '@/components/organisms/DeleteUser.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const shareEnabled = ref<boolean>(user.value.share_enabled ?? false);
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="settings-stack">
        <Heading
            variant="small"
            title="Profile"
            description="Update your name and email address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="settings-form"
            v-slot="{ errors, processing }"
        >
            <div class="settings-field">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    class="settings-input"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="settings-error" :message="errors.name" />
            </div>

            <div class="settings-field">
                <Label for="display_name"
                    >Display name
                    <span class="settings-optional">(optional)</span></Label
                >
                <Input
                    id="display_name"
                    class="settings-input"
                    name="display_name"
                    :default-value="user.display_name ?? ''"
                    autocomplete="nickname"
                    placeholder="Shown on your shared list"
                />
                <InputError
                    class="settings-error"
                    :message="errors.display_name"
                />
            </div>

            <div class="settings-field">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="settings-input"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="settings-error" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="settings-verify">
                    Your email address is unverified.
                    <Link :href="send()" as="button" class="text-link">
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="settings-verify-sent"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="settings-field">
                <div class="settings-checkbox-row">
                    <Checkbox
                        id="share_enabled"
                        v-model="shareEnabled"
                        class="settings-checkbox"
                    />
                    <input
                        type="hidden"
                        name="share_enabled"
                        :value="shareEnabled ? 1 : 0"
                    />
                    <div class="settings-checkbox-body">
                        <Label for="share_enabled">Enable public sharing</Label>
                        <p class="settings-hint">
                            Let anyone with your share link view your parks list
                            and map. You can turn this off at any time.
                        </p>
                    </div>
                </div>
                <InputError
                    class="settings-error"
                    :message="errors.share_enabled"
                />
            </div>

            <div class="settings-actions">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
