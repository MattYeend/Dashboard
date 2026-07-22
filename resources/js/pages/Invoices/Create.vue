<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import type { InvoiceStatus, UserOption } from '@/types';
import InvoiceForm from './components/InvoiceForm.vue';
import { store as invoicesStore } from '@/routes/invoices';

defineProps<{
    statuses: InvoiceStatus[];
    companies: UserOption[];
}>();

const form = useForm({
    invoice_number: '',
    company_id: null,
    order_id: null,
    status_id: null,
    issue_date: null,
    due_date: null,
    subtotal: null,
    tax_total: null,
    total: null,
    currency: 'GBP',
    notes: null,
    contact: {
        phone: null,
        email: null,
        address: null,
        city: null,
        postal_code: null,
        country: null,
    },
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        company_id: numberOrNull(data.company_id),
        order_id: numberOrNull(data.order_id),
        status_id: numberOrNull(data.status_id),
        issue_date: nullIfBlank(data.issue_date),
        due_date: nullIfBlank(data.due_date),
        notes: nullIfBlank(data.notes),
    })).post(invoicesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Invoice
            </h1>
            <InvoiceForm
                v-model:invoice-number="form.invoice_number"
                v-model:company-id="form.company_id"
                v-model:status-id="form.status_id"
                v-model:issue-date="form.issue_date"
                v-model:due-date="form.due_date"
                v-model:subtotal="form.subtotal"
                v-model:tax-total="form.tax_total"
                v-model:total="form.total"
                v-model:currency="form.currency"
                v-model:notes="form.notes"
                v-model:contact="form.contact"
                :is-editing="false"
                :processing="form.processing"
                :statuses="statuses"
                :companies="companies"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
