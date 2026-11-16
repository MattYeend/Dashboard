<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PaymentForm from '@/pages/Payments/components/PaymentForm.vue';
import { show, update } from '@/routes/payments';
import type {
    PayableInvoiceOption,
    Payment,
    PaymentMethodOption,
} from '@/types';
import type { PaymentFormData } from '@/types/forms';

const props = defineProps<{
    payment: Payment;
    invoices: PayableInvoiceOption[];
    methods: PaymentMethodOption[];
}>();

const form = useForm<PaymentFormData>({
    invoice_id: String(props.payment.invoice_id),
    method: props.payment.method,
    amount: props.payment.amount,
    paid_at: props.payment.paid_at.slice(0, 16),
    reference: props.payment.reference,
    notes: props.payment.notes,
});

/**
 * Submit only the editable fields.
 */
function submit(): void {
    form
        .transform((data) => ({
            method: data.method,
            paid_at: data.paid_at,
            reference: data.reference?.trim() || null,
            notes: data.notes?.trim() || null,
        }))
        .put(update.url(props.payment.id));
}
</script>

<template>
    <Head :title="`Edit payment #${payment.id}`" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Payment</h1>

            <PaymentForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :invoices="invoices"
                :methods="methods"
                lock-invoice
                lock-amount
                submit-label="Save changes"
                :cancel-href="show.url(payment.id)"
                @submit="submit"
            />
        </div>
    </div>
</template>