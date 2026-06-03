<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Contact } from '@/types';

interface Props {
    contacts: {
        data: Contact[];
        links: Record<string, string | null>;
        meta: Record<string, unknown>;
    };
}

defineProps<Props>();

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(route('contacts.destroy', id));
    }
}
</script>

<template>
    <AppLayout title="Contacts">
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4 flex items-centre justify-between">
                    <h1 class="text-2xl font-semibold text-grey-900">Contacts</h1>
                    <a :href="route('contacts.create')" class="inline-flex items-centre rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm">
                        Add Contact
                    </a>
                </div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-grey-300">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">City</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-grey-500">Country</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-grey-200">
                            <tr v-for="contact in contacts.data" :key="contact.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-900">{{ contact.email ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-500">{{ contact.phone ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-500">{{ contact.city ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-grey-500">{{ contact.country ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a :href="route('contacts.show', contact.id)" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <a :href="route('contacts.edit', contact.id)" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <button type="button" class="text-red-600 hover:text-red-900" @click="destroy(contact.id)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="contacts.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-centre text-sm text-grey-500">No contacts found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>