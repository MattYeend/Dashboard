<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, update } from '@/routes/payment-statuses';
import type { PaymentStatus } from '@/types';
import type { PaymentStatusFormData } from '@/types/forms/payment-statuses';

import PaymentStatusForm from './components/PaymentStatusForm.vue';

const props = defineProps<{
    paymentStatus: PaymentStatus;
}>();

const form = useForm<PaymentStatusFormData>({
    title: props.paymentStatus.title,
    description: props.paymentStatus.description,
    background_colour: props.paymentStatus.background_colour,
    text_colour: props.paymentStatus.text_colour,
});

function submit(): void {
    form.put(update.url(props.paymentStatus.id));
}
</script>

<template>
    <Head :title="`Edit ${paymentStatus.title}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Payment statuses', href: index.url() },
            { title: paymentStatus.title, href: show.url(paymentStatus.id) },
            { title: 'Edit', href: '#' },
        ]"
    >
        <div class="mx-auto w-full max-w-3xl p-4">
            <h1 class="mb-6 text-2xl font-semibold">Edit payment status</h1>

            <PaymentStatusForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :is-system="paymentStatus.is_system"
                submit-label="Save changes"
                :cancel-href="show.url(paymentStatus.id)"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
