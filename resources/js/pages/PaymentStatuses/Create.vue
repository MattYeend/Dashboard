<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { index, store } from '@/routes/payment-statuses';
import type { PaymentStatusFormData } from '@/types/forms/payment-statuses';

import PaymentStatusForm from './components/PaymentStatusForm.vue';

const form = useForm<PaymentStatusFormData>({
    title: '',
    description: null,
    background_colour: '#e2e8f0',
    text_colour: '#1a202c',
});

function submit(): void {
    form.post(store.url());
}
</script>

<template>
    <Head title="Create payment status" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Payment statuses', href: index.url() },
            { title: 'Create', href: '#' },
        ]"
    >
        <div class="mx-auto w-full max-w-3xl p-4">
            <h1 class="mb-6 text-2xl font-semibold">Create payment status</h1>

            <PaymentStatusForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :is-system="false"
                submit-label="Create payment status"
                :cancel-href="index.url()"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
