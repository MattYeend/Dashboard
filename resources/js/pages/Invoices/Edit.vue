<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import type { Invoice, InvoiceStatus, UserOption } from '@/types';
import InvoiceForm from './components/InvoiceForm.vue';
import { update as invoicesUpdate } from '@/routes/invoices';

interface Props {
    invoice: Invoice;
    statuses: InvoiceStatus[];
    companies: UserOption[];
}

const props = defineProps<Props>();

const form = useForm({
    invoice_number: props.invoice.invoice_number,
    company_id: props.invoice.company_id,
    order_id: props.invoice.order_id,
    status_id: props.invoice.status_id,
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date,
    subtotal: props.invoice.subtotal,
    tax_total: props.invoice.tax_total,
    total: props.invoice.total,
    currency: props.invoice.currency,
    notes: props.invoice.notes,
    contact: {
        phone: props.invoice.contact?.phone ?? null,
        email: props.invoice.contact?.email ?? null,
        address: props.invoice.contact?.address ?? null,
        city: props.invoice.contact?.city ?? null,
        postal_code: props.invoice.contact?.postal_code ?? null,
        country: props.invoice.contact?.country ?? null,
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
    })).put(invoicesUpdate.url(props.invoice.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Invoice
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
                :is-editing="true"
                :processing="form.processing"
                :statuses="statuses"
                :companies="companies"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
