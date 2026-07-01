<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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

        <ContactTypeForm
            v-model:contactable-type="contactableType"
            v-model:contactable-id="contactableId"
            :contactable-types="contactableTypes"
            :contactable-options="contactableOptions"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Link :href="contactsIndex.url()" class="text-sm font-medium">
                Cancel
            </Link>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Contact' : 'Create Contact' }}
            </Button>
        </div>
    </form>
</template>
