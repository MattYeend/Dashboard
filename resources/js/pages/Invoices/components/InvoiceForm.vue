<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import InvoiceBasicDetailsForm from '@/pages/Invoices/components/InvoiceBasicDetailsForm.vue';
import InvoiceContactDetailsForm from '@/pages/Invoices/components/InvoiceContactDetailsForm.vue';
import InvoiceDateDetailsForm from '@/pages/Invoices/components/InvoiceDateDetailsForm.vue';
import InvoiceTotalsDetailsForm from '@/pages/Invoices/components/InvoiceTotalsDetailsForm.vue';
import type { InvoiceStatus, UserOption } from '@/types';
import { index as invoicesIndex } from '@/routes/invoices';

interface InvoiceContact {
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    postal_code: string | null;
    country: string | null;
}

interface InvoiceFormData {
    invoice_number: string;
    company_id: number | null;
    order_id: number | null;
    status_id: number | null;
    issue_date: string | null;
    due_date: string | null;
    subtotal: number | null;
    tax_total: number | null;
    total: number | null;
    currency: string;
    notes: string | null;
    contact: InvoiceContact;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    statuses: InvoiceStatus[];
    companies: UserOption[];
    errors: Partial<InertiaFormProps<InvoiceFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const invoiceNumber = defineModel<string>('invoiceNumber', { required: true });
const companyId = defineModel<number | null>('companyId', { default: null });
const statusId = defineModel<number | null>('statusId', { default: null });
const issueDate = defineModel<string | null>('issueDate', { default: null });
const dueDate = defineModel<string | null>('dueDate', { default: null });
const subtotal = defineModel<number | null>('subtotal', { default: null });
const taxTotal = defineModel<number | null>('taxTotal', { default: null });
const total = defineModel<number | null>('total', { default: null });
const currency = defineModel<string>('currency', { required: true });
const notes = defineModel<string | null>('notes', { default: null });
const contact = defineModel<InvoiceContact>('contact', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <InvoiceBasicDetailsForm
            v-model:invoice-number="invoiceNumber"
            v-model:company-id="companyId"
            v-model:status-id="statusId"
            :statuses="statuses"
            :companies="companies"
            :errors="errors"
        />
        <InvoiceDateDetailsForm
            v-model:issue-date="issueDate"
            v-model:due-date="dueDate"
            :errors="errors"
        />
        <InvoiceContactDetailsForm v-model:contact="contact" :errors="errors" />
        <InvoiceTotalsDetailsForm
            v-model:subtotal="subtotal"
            v-model:tax-total="taxTotal"
            v-model:total="total"
            v-model:currency="currency"
            v-model:notes="notes"
            :errors="errors"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="invoicesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Invoice' : 'Create Invoice' }}
            </Button>
        </div>
    </form>
</template>
