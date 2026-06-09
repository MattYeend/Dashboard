<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    show as usersShow,
    create as usersCreate,
    edit as usersEdit,
    destroy as usersDestroy,
} from '@/routes/users';
import type { Pagination, PermissionsMeta, User } from '@/types';

interface Props {
    users: {
        data: User[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: Pagination;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: string[];
    trash_filters: string[];
}

defineProps<Props>();

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(usersDestroy.url(id));
    }
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="items-centre mb-4 flex justify-between">
                <h1 class="text-grey-900 text-2xl font-semibold">Users</h1>
                <Link
                    :href="usersCreate.url()"
                    class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
                >
                    Add User
                </Link>
            </div>

            <div
                class="ring-opacity-5 overflow-hidden shadow ring-1 ring-black sm:rounded-lg"
            >
                <table class="divide-grey-300 min-w-full divide-y">
                    <thead>
                        <tr>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Name
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Email
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Role
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Created
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-grey-200 divide-y">
                        <tr v-for="user in users.data ?? []" :key="user.id">
                            <td
                                class="text-grey-900 px-6 py-4 text-sm font-medium whitespace-nowrap"
                            >
                                {{ user.name }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ user.email }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap capitalize"
                            >
                                {{ user.role.replace('_', ' ') }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td
                                class="space-x-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                            >
                                <Link
                                    :href="usersShow.url(user.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="usersEdit.url(user.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:text-red-900"
                                    @click="destroy(user.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!users.data?.length">
                            <td
                                colspan="5"
                                class="text-centre text-grey-500 px-6 py-4 text-sm"
                            >
                                No users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div
                    v-if="users.meta.last_page > 1"
                    class="mt-4 flex items-center justify-between"
                >
                    <p class="text-grey-500 text-sm">
                        Showing {{ users.meta.from ?? 0 }} to
                        {{ users.meta.to ?? 0 }} of {{ users.meta.total }} users
                    </p>
                    <div class="flex gap-x-1">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url ?? ''"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.url === null
                                    ? 'pointer-events-none opacity-40'
                                    : 'hover:bg-accent',
                                link.active ? 'font-semibold' : '',
                            ]"
                            preserve-scroll
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
