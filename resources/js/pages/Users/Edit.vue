<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import UserForm from '@/pages/Users/components/UserForm.vue';
import { update as usersUpdate } from '@/routes/users';
import type { User } from '@/types';

interface Props {
    user: User;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

function submit(): void {
    form.put(usersUpdate.url(props.user.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-gray-300 mb-6 text-2xl font-semibold">Edit User</h1>
            <UserForm
                v-model:name="form.name"
                v-model:email="form.email"
                v-model:password="form.password"
                v-model:password-confirmation="form.password_confirmation"
                v-model:role="form.role"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
