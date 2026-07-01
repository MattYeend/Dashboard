<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ContactAddressDetails from '@/pages/Contacts/components/ContactAddressDetails.vue';
import ContactAuditDetails from '@/pages/Contacts/components/ContactAuditDetails.vue';
import ContactBasicDetails from '@/pages/Contacts/components/ContactBasicDetails.vue';
import {
    edit as contactsEdit,
    destroy as contactsDestroy,
    index as contactsIndex,
} from '@/routes/contacts';
import type { Contact } from '@/types';

interface Props {
    contact: Contact;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(contactsDestroy.url(props.contact.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">Contact</h1>
                <div class="space-x-2">
                    <Link
                        :href="contactsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="contactsEdit.url(props.contact.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
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

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete contact"
            description="This contact will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>