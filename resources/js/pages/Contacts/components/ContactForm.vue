<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
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
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<ContactFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const email = defineModel<string>('email', { required: true });
const phone = defineModel<string>('phone', { required: true });
const address = defineModel<string>('address', { required: true });
const city = defineModel<string>('city', { required: true });
const postalCode = defineModel<string>('postalCode', { required: true });
const country = defineModel<string>('country', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <ContactBasicDetailsForm
            v-model:email="email"
            v-model:phone="phone"
            :errors="errors"
        />
        <ContactAddressDetailsForm
            v-model:address="address"
            v-model:city="city"
            v-model:postal-code="postalCode"
            v-model:country="country"
            :errors="errors"
        />

        <div class="items-centre flex justify-end space-x-3">
            <a
                :href="route('contacts.index')"
                class="text-grey-700 rounded-md px-4 py-2 text-sm font-medium"
                >Cancel</a
            >
            <button
                type="submit"
                :disabled="processing"
                class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ isEditing ? 'Update Contact' : 'Create Contact' }}
            </button>
        </div>
    </form>
</template>
