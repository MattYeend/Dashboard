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
    isEditing: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-grey-700">Name</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-grey-700">Email Address</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-grey-700">
                {{ isEditing ? 'New Password (leave blank to keep current)' : 'Password' }}
            </label>
            <input
                id="password"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-grey-700">Confirm Password</label>
            <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm"
            />
        </div>
    </div>
</template>