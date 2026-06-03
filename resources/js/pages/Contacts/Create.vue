<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ContactForm from '@/pages/Contacts/components/ContactForm.vue';

const form = useForm({
    phone: '',
    email: '',
    address: '',
    city: '',
    postal_code: '',
    country: '',
});

function submit(): void {
    form.post(route('contacts.store'));
}
</script>

<template>
    <AppLayout title="Create Contact">
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-grey-900 mb-6 text-2xl font-semibold">
                    Create Contact
                </h1>
                <ContactForm
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
    </AppLayout>
</template>
