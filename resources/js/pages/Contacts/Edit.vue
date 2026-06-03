<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Contact } from '@/types';
import ContactForm from '@/pages/Contacts/components/ContactForm.vue';

interface Props {
    contact: Contact;
}

const props = defineProps<Props>();

const form = useForm({
    phone: props.contact.phone ?? '',
    email: props.contact.email ?? '',
    address: props.contact.address ?? '',
    city: props.contact.city ?? '',
    postal_code: props.contact.postal_code ?? '',
    country: props.contact.country ?? '',
});

function submit(): void {
    form.put(route('contacts.update', props.contact.id));
}
</script>

<template>
    <AppLayout title="Edit Contact">
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-grey-900 mb-6 text-2xl font-semibold">
                    Edit Contact
                </h1>
                <ContactForm
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
    </AppLayout>
</template>
