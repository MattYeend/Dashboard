<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import ContactAddressDetailsForm from '@/pages/Contacts/components/ContactAddressDetailsForm.vue';
import ContactBasicDetailsForm from '@/pages/Contacts/components/ContactBasicDetailsForm.vue';
import ContactTypeForm from '@/pages/Contacts/components/ContactTypeForm.vue';
import { index as contactsIndex } from '@/routes/contacts';

interface ContactFormData {
    phone: string;
    email: string;
    address: string;
    city: string;
    postal_code: string;
    country: string;
    contactable_type: string;
    contactable_id: number | null;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<ContactFormData>['errors']>;

    contactableTypes: { value: string; label: string }[];
    contactableOptions: { value: number; label: string }[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const email = defineModel<string>('email', { required: true });
const phone = defineModel<string>('phone', { required: true });
const address = defineModel<string>('address', { required: true });
const city = defineModel<string>('city', { required: true });
const postalCode = defineModel<string>('postalCode', { required: true });
const country = defineModel<string>('country', { required: true });

const contactableType = defineModel<string>('contactableType', {
    required: true,
});
const contactableId = defineModel<number | null>('contactableId', {
    required: true,
});
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <!-- Basic -->
        <ContactBasicDetailsForm
            v-model:email="email"
            v-model:phone="phone"
            :errors="errors"
        />

        <!-- Address -->
        <ContactAddressDetailsForm
            v-model:address="address"
            v-model:city="city"
            v-model:postal-code="postalCode"
            v-model:country="country"
            :errors="errors"
        />

        <!-- Type & Owner -->
        <ContactTypeForm
            v-model:contactable-type="contactableType"
            v-model:contactable-id="contactableId"
            :contactable-types="contactableTypes"
            :contactable-options="contactableOptions"
            :errors="errors"
        />

        <!-- ACTIONS -->
        <div class="flex justify-end space-x-3">
            <a
                :href="contactsIndex.url()"
                class="rounded-md px-4 py-2 text-sm font-medium"
            >
                Cancel
            </a>

            <button
                type="submit"
                :disabled="processing"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ isEditing ? 'Update Contact' : 'Create Contact' }}
            </button>
        </div>
    </form>
</template>
