<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { User } from '@/types';
import UserForm from '@/pages/Users/components/UserForm.vue';

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
    form.put(route('users.update', props.user.id));
}
</script>

<template>
    <AppLayout title="Edit User">
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="mb-6 text-2xl font-semibold text-grey-900">Edit User</h1>
                <UserForm
                    :form="form"
                    :is-editing="true"
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>