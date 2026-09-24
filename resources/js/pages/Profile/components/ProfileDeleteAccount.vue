<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy } from '@/routes/profile';

interface DeleteAccountFormData {
    password: string;
}

const confirming = ref(false);
const form = useForm<DeleteAccountFormData>({ password: '' });

function submit(): void {
    form.delete(destroy().url, {
        preserveScroll: true,
        onError: () => form.reset('password'),
    });
}

function cancel(): void {
    confirming.value = false;
    form.reset();
    form.clearErrors();
}
</script>

<template>
    <section class="flex flex-col gap-4 rounded-lg border border-gray-500 p-4">
        <h2 class="text-sm font-medium text-gray-300">Delete account</h2>
        <p class="text-sm text-gray-400">
            Your account will be deactivated and you will be signed out everywhere. An administrator can restore it.
        </p>

        <div v-if="!confirming">
            <Button type="button" variant="destructive" @click="confirming = true">Delete my account</Button>
        </div>

        <form v-else class="flex flex-col gap-3" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="delete-password">Confirm your password</Label>
                <Input id="delete-password" v-model="form.password" type="password" autocomplete="current-password" />
                <InputError :message="form.errors.password" />
            </div>
            <div class="flex gap-2">
                <Button type="submit" variant="destructive" :disabled="form.processing">Permanently deactivate</Button>
                <Button type="button" variant="outline" @click="cancel">Cancel</Button>
            </div>
        </form>
    </section>
</template>