<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

import ContactForm from '@/pages/Contacts/components/ContactForm.vue';
import { store as contactsStore } from '@/routes/contacts';

interface Props {
    contactable_types: { value: string; label: string }[];
}

defineProps<Props>();

const form = useForm({
    contactable_type: 'user',
    contactable_id: null as number | null,

    phone: '',
    email: '',
    address: '',
    city: '',
    postal_code: '',
    country: '',
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
    form.post(contactsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-900">
                Create Contact
            </h1>

            <ContactForm
                v-model:contactable-type="form.contactable_type"
                v-model:contactable-id="form.contactable_id"

                :contactable-types="contactable_types"
                :contactable-options="contactableOptions"

                v-model:email="form.email"
                v-model:phone="form.phone"
                v-model:address="form.address"
                v-model:city="form.city"
                v-model:postal-code="form.postal_code"
                v-model:country="form.country"

                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>