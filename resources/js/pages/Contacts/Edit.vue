<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

import ContactForm from '@/pages/Contacts/components/ContactForm.vue';
import { update as contactsUpdate } from '@/routes/contacts';
import type { Contact } from '@/types';

interface Props {
    contact: Contact & { contactable_type_key: string };
    contactableTypes: { value: string; label: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    contactable_type: props.contact.contactable_type_key ?? props.contact.contactable_type,
    contactable_id: props.contact.contactable_id as number | null,
    phone: props.contact.phone ?? '',
    email: props.contact.email ?? '',
    address: props.contact.address ?? '',
    city: props.contact.city ?? '',
    postal_code: props.contact.postal_code ?? '',
    country: props.contact.country ?? '',
});

interface ContactableOption {
    value: number;
    label: string;
}

const contactableOptions = ref<ContactableOption[]>([]);

watch(
    () => form.contactable_type,
    async (type: string) => {
        const res = await axios.get('/contacts/contactable-options', {
            params: { type },
        });

        contactableOptions.value = res.data;
        form.contactable_id = null;
    },
    { immediate: true }
);

function submit(): void {
    form.put(contactsUpdate.url(props.contact.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">
                Edit Contact
            </h1>

            <ContactForm
                v-model:contactable-type="form.contactable_type"
                v-model:contactable-id="form.contactable_id"
                :contactable-types="contactableTypes"
                :contactable-options="contactableOptions"
                v-model:email="form.email"
                v-model:phone="form.phone"
                v-model:address="form.address"
                v-model:city="form.city"
                v-model:postal-code="form.postal_code"
                v-model:country="form.country"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>