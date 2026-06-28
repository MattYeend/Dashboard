<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import UserForm from '@/pages/Users/components/UserForm.vue';
import { store as usersStore } from '@/routes/users';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

function submit(): void {
    form.post(usersStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-gray-900 mb-6 text-2xl font-semibold">
                Create User
            </h1>
            <UserForm
                v-model:name="form.name"
                v-model:email="form.email"
                v-model:password="form.password"
                v-model:password-confirmation="form.password_confirmation"
                v-model:role="form.role"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
