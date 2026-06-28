<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import EmptyRow from '@/components/table/EmptyRow.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import {
    index as contactsIndex,
    show as contactsShow,
    create as contactsCreate,
    edit as contactsEdit,
    destroy as contactsDestroy,
} from '@/routes/contacts';
import type {
    Pagination as PaginationMeta,
    PermissionsMeta,
    Contact,
} from '@/types';

interface Props {
    contacts: {
        data: Contact[];
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
    sort_by: urlParams.get('sort_by') ?? 'email',
    sort_direction: urlParams.get('sort_direction') ?? 'asc',
});

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search contacts…',
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
    router.get(contactsIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(contactsDestroy.url(id));
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Contacts"
                :create-href="contactsCreate.url()"
                create-label="Add Contact"
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
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Type<!-- ADD column -->
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Contact Of
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Email
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Phone
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                City
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Country
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-grey-200 divide-y">
                        <EmptyRow
                            v-if="!contacts.data?.length"
                            :colspan="7"
                            message="No contacts found."
                        />
                        <tr
                            v-for="contact in contacts.data ?? []"
                            :key="contact.id"
                        >
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ contact.contactable_type_label ?? '—' }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ contact.contactable_name ?? '—' }}
                            </td>
                            <td
                                class="text-gray-900 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ contact.email ?? '—' }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ contact.phone ?? '—' }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ contact.city ?? '—' }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ contact.country ?? '—' }}
                            </td>
                            <td
                                class="space-x-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                            >
                                <Link :href="contactsShow.url(contact.id)">
                                    View
                                </Link>
                                <Link :href="contactsEdit.url(contact.id)">
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:text-red-900"
                                    @click="destroy(contact.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                :meta="contacts.meta"
                :links="contacts.links"
                resource-label="contacts"
            />
        </div>
    </div>
</template>
