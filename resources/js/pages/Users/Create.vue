<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import UserForm from '@/pages/Users/components/UserForm.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

function submit(): void {
    form.post(route('users.store'));
}
</script>

<template>
    <AppLayout title="Create User">
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="mb-6 text-2xl font-semibold text-grey-900">Create User</h1>
                <UserForm
                    :form="form"
                    :is-editing="false"
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>