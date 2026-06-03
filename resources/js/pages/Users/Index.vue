<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { User } from '@/types';

interface Props {
    users: {
        data: User[];
        links: Record<string, string | null>;
        meta: Record<string, unknown>;
    };
}

defineProps<Props>();

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', id));
    }
}
</script>

<template>
    <AppLayout title="Users">
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4 flex items-centre justify-between">
                    <h1 class="text-2xl font-semibold text-grey-900">Users</h1>
                    <a
                        :href="route('users.create')"
                        class="inline-flex items-centre rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
                    >
                        Add User
                    </a>
                </div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-grey-300">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Created</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-grey-200">
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-grey-900">{{ user.name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-500">{{ user.email }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-500 capitalize">{{ user.role.replace('_', ' ') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-500">{{ user.created_at }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a :href="route('users.show', user.id)" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <a :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <button
                                        type="button"
                                        class="text-red-600 hover:text-red-900"
                                        @click="destroy(user.id)"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-centre text-sm text-grey-500">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>