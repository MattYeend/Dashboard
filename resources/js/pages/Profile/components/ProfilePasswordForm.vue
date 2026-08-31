<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

defineProps<{
    errors: Partial<
        Record<
            'current_password' | 'password' | 'password_confirmation',
            string
        >
    >;
}>();

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
        <div class="space-y-2">
            <Label for="current_password">Current password</Label>
            <Input
                id="current_password"
                v-model="currentPassword"
                type="password"
                autocomplete="current-password"
                required
            />
            <InputError :message="errors.current_password" />
        </div>

        <div class="space-y-2">
            <Label for="password">New password</Label>
            <Input
                id="password"
                v-model="password"
                type="password"
                autocomplete="new-password"
                required
            />
            <InputError :message="errors.password" />
        </div>

        <div class="space-y-2">
            <Label for="password_confirmation">Confirm new password</Label>
            <Input
                id="password_confirmation"
                v-model="passwordConfirmation"
                type="password"
                autocomplete="new-password"
                required
            />
            <InputError :message="errors.password_confirmation" />
        </div>
    </div>
</template>
