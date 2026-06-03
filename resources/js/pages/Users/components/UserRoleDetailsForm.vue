<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

interface UserFormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: string;
}

interface Props {
    form: InertiaForm<UserFormData>;
}

defineProps<Props>();

const roles = [
    { value: 'user', label: 'User' },
    { value: 'admin', label: 'Admin' },
    { value: 'super_admin', label: 'Super Admin' },
];
</script>

<template>
    <div>
        <label for="role" class="block text-sm font-medium text-grey-700">Role</label>
        <select
            id="role"
            v-model="form.role"
            class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm"
        >
            <option v-for="roleOption in roles" :key="roleOption.value" :value="roleOption.value">
                {{ roleOption.label }}
            </option>
        </select>
        <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
    </div>
</template>