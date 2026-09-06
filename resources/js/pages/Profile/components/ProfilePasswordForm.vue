<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface ProfilePasswordFormData {
    current_password: string;
    password: string;
    password_confirmation: string;
}

interface Props {
    errors: Partial<InertiaFormProps<ProfilePasswordFormData>['errors']>;
}

defineProps<Props>();

const currentPassword = defineModel<string>('currentPassword', {
    required: true,
});
const password = defineModel<string>('password', { required: true });
const passwordConfirmation = defineModel<string>('passwordConfirmation', {
    required: true,
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="current_password">Current password</Label>
            <Input
                id="current_password"
                v-model="currentPassword"
                type="password"
                class="mt-1 block w-full"
                autocomplete="current-password"
                placeholder="Current password"
            />
            <InputError :message="errors.current_password" />
        </div>

        <div>
            <Label for="password">New password</Label>
            <Input
                id="password"
                v-model="password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                placeholder="New password"
            />
            <InputError :message="errors.password" />
        </div>

        <div>
            <Label for="password_confirmation">Confirm password</Label>
            <Input
                id="password_confirmation"
                v-model="passwordConfirmation"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                placeholder="Confirm password"
            />
            <InputError :message="errors.password_confirmation" />
        </div>
    </div>
</template>
