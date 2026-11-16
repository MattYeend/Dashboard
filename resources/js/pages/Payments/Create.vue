<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PaymentForm from '@/pages/Payments/components/PaymentForm.vue';
import { index, store } from '@/routes/payments';
import type { PayableInvoiceOption, PaymentMethodOption } from '@/types';
import type { PaymentFormData } from '@/types/forms';

const props = defineProps<{
    invoice: PayableInvoiceOption | null;
    invoices: PayableInvoiceOption[];
    methods: PaymentMethodOption[];
}>();

/**
 * Format the current time for a datetime-local input.
 */
function nowLocal(): string {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

    return now.toISOString().slice(0, 16);
}

const form = useForm<PaymentFormData>({
    invoice_id: props.invoice ? String(props.invoice.id) : '',
    method: 'bank_transfer',
    amount: props.invoice?.balance_due ?? '',
    paid_at: nowLocal(),
    reference: null,
    notes: null,
});

/**
 * Submit the payment once. The button is disabled while processing to stop
 * a double submit.
 */
function submit(): void {
    form
        .transform((data) => ({
            ...data,
            reference: data.reference?.trim() || null,
            notes: data.notes?.trim() || null,
        }))
        .post(store.url());
}
</script>

<template>
    <Head title="Record payment" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Record Payment</h1>

            <PaymentForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :invoices="invoices"
                :methods="methods"
                :lock-invoice="invoice !== null"
                :lock-amount="false"
                submit-label="Record payment"
                :cancel-href="index.url()"
                @submit="submit"
            />
        </div>
    </div>
</template>