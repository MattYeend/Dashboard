<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    show as contactsShow,
    create as contactsCreate,
    edit as contactsEdit,
    destroy as contactsDestroy,
} from '@/routes/contacts';
import type { Pagination, PermissionsMeta, Contact } from '@/types';

interface Props {
    contacts: {
        data: Contact[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: Pagination;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: string[];
    trash_filters: string[];
}

defineProps<Props>();

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(contactsDestroy.url(id));
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="items-centre mb-4 flex justify-between">
                <h1 class="text-grey-900 text-2xl font-semibold">Contacts</h1>
                <Link
                    :href="contactsCreate.url()"
                    class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
                >
                    Add Contact
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
                        <tr
                            v-for="contact in contacts.data ?? []"
                            :key="contact.id"
                        >
                            <td
                                class="text-grey-900 px-6 py-4 text-sm whitespace-nowrap"
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
                                <Link
                                    :href="contactsShow.url(contact.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="contactsEdit.url(contact.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
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
                        <tr v-if="!contacts.data?.length">
                            <td
                                colspan="5"
                                class="text-centre text-grey-500 px-6 py-4 text-sm"
                            >
                                No contacts found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="contacts.meta.last_page > 1"
                class="mt-4 flex items-center justify-between"
            >
                <p class="text-grey-500 text-sm">
                    Showing {{ contacts.meta.from ?? 0 }} to
                    {{ contacts.meta.to ?? 0 }} of
                    {{ contacts.meta.total }} contacts
                </p>
                <div class="flex gap-x-1">
                    <Link
                        v-for="link in contacts.links"
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
</template>
