<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import EmptyRow from '@/components/table/EmptyRow.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import {
    index as usersIndex,
    show as usersShow,
    create as usersCreate,
    edit as usersEdit,
    destroy as usersDestroy,
} from '@/routes/users';
import type {
    Pagination as PaginationMeta,
    PermissionsMeta,
    User,
} from '@/types';

interface Props {
    users: {
        data: User[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const urlParams = new URLSearchParams(window.location.search);

const filters = ref({
    search: urlParams.get('search') ?? '',
    trashed: urlParams.get('trashed') ?? '',
    sort_by: urlParams.get('sort_by') ?? 'name',
    sort_direction: urlParams.get('sort_direction') ?? 'asc',
});

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search users…',
    },
    {
        key: 'trashed',
        type: 'select' as const,
        get options() {
            return Object.entries(props.trash_filters).map(
                ([value, label]) => ({
                    value,
                    label,
                }),
            );
        },
    },
    {
        key: 'sort_by',
        type: 'select' as const,
        get options() {
            return Object.entries(props.sort_fields).map(([value, label]) => ({
                value,
                label: `Sort by ${label}`,
            }));
        },
    },
    {
        key: 'sort_direction',
        type: 'select' as const,
        options: [
            { value: 'asc', label: 'Ascending' },
            { value: 'desc', label: 'Descending' },
        ],
    },
];

function applyFilters(): void {
    router.get(usersIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

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
            <IndexHeader
                title="Users"
                :create-href="usersCreate.url()"
                create-label="Add User"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <div
                class="ring-opacity-5 overflow-hidden shadow ring-1 ring-black sm:rounded-lg"
            >
                <table class="divide-grey-300 min-w-full divide-y">
                    <thead>
                        <tr>
                            <th
                                class="text-gray-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Name
                            </th>
                            <th
                                class="text-gray-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Email
                            </th>
                            <th
                                class="text-gray-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Role
                            </th>
                            <th
                                class="text-gray-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Created
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-grey-200 divide-y">
                        <EmptyRow
                            v-if="!users.data?.length"
                            :colspan="5"
                            message="No users found."
                        />
                        <tr v-for="user in users.data ?? []" :key="user.id">
                            <td
                                class="text-gray-900 px-6 py-4 text-sm font-medium whitespace-nowrap"
                            >
                                {{ user.name }}
                            </td>
                            <td
                                class="text-gray-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ user.email }}
                            </td>
                            <td
                                class="text-gray-500 px-6 py-4 text-sm whitespace-nowrap capitalize"
                            >
                                {{ user.role.replace('_', ' ') }}
                            </td>
                            <td
                                class="text-gray-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td
                                class="space-x-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                            >
                                <Link :href="usersShow.url(user.id)">
                                    View
                                </Link>
                                <Link :href="usersEdit.url(user.id)">
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
                    </tbody>
                </table>
            </div>

            <Pagination
                :meta="users.meta"
                :links="users.links"
                resource-label="users"
            />
        </div>
    </div>
</template>
