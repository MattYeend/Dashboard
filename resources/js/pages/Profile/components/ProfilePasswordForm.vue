<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/profile/password';

interface PasswordFormData {
    current_password: string;
    password: string;
    password_confirmation: string;
}

const form = useForm<PasswordFormData>({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.put(update().url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => form.reset('password', 'password_confirmation', 'current_password'),
    });
}
</script>

<template>
    <form class="flex flex-col gap-4 rounded-lg border border-gray-500 p-4" @submit.prevent="submit">
        <h2 class="text-sm font-medium text-gray-300">Change password</h2>

        <div class="grid gap-2">
            <Label for="current-password">Current password</Label>
            <Input
                id="current-password"
                v-model="form.current_password"
                type="password"
                autocomplete="current-password"
            />
            <InputError :message="form.errors.current_password" />
        </div>

        <div class="grid gap-2">
            <Label for="new-password">New password</Label>
            <Input id="new-password" v-model="form.password" type="password" autocomplete="new-password" />
            <InputError :message="form.errors.password" />
        </div>

        <div class="grid gap-2">
            <Label for="confirm-password">Confirm new password</Label>
            <Input
                id="confirm-password"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
            />
        </div>

        <p class="text-xs text-gray-400">
            Changing your password signs you out of all other devices.
        </p>

        <div>
            <Button type="submit" :disabled="form.processing">Update password</Button>
        </div>
    </form>
</template>