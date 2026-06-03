<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Contact } from '@/types';
import ContactAddressDetails from '@/pages/Contacts/components/ContactAddressDetails.vue';
import ContactAuditDetails from '@/pages/Contacts/components/ContactAuditDetails.vue';
import ContactBasicDetails from '@/pages/Contacts/components/ContactBasicDetails.vue';

interface Props {
    contact: Contact;
}

const props = defineProps<Props>();

function destroy(): void {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(route('contacts.destroy', props.contact.id));
    }
}
</script>

<template>
    <AppLayout title="Contact">
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="items-centre mb-6 flex justify-between">
                    <h1 class="text-grey-900 text-2xl font-semibold">
                        Contact
                    </h1>
                    <div class="space-x-2">
                        <a
                            :href="route('contacts.edit', contact.id)"
                            class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium"
                            >Edit</a
                        >
                        <button
                            type="button"
                            class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-red-600"
                            @click="destroy"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <ContactBasicDetails :contact="contact" />
                    <ContactAddressDetails :contact="contact" />
                    <ContactAuditDetails :contact="contact" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
