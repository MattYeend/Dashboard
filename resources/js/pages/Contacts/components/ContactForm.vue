<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import ContactAddressDetailsForm from '@/pages/Contacts/components/ContactAddressDetailsForm.vue';
import ContactBasicDetailsForm from '@/pages/Contacts/components/ContactBasicDetailsForm.vue';

interface ContactFormData {
    phone: string;
    email: string;
    address: string;
    city: string;
    postal_code: string;
    country: string;
}

interface Props {
    form: InertiaForm<ContactFormData>;
    isEditing: boolean;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <ContactBasicDetailsForm :form="form" />
        <ContactAddressDetailsForm :form="form" />

        <div class="flex items-centre justify-end space-x-3">
            <a :href="route('contacts.index')" class="rounded-md px-4 py-2 text-sm font-medium text-grey-700">Cancel</a>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-centre rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ isEditing ? 'Update Contact' : 'Create Contact' }}
            </button>
        </div>
    </form>
</template>