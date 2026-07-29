<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as invoiceItemsStore } from '@/routes/invoices/items';
import type { Invoice } from '@/types';
import InvoiceItemForm from './components/InvoiceItemForm.vue';

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const form = useForm({
    description: '',
    quantity: 1,
    unit_price: 0,
    tax_rate: 0,
    position: 0,
});

function submit(): void {
    form.post(invoiceItemsStore.url({ invoice: props.invoice.id }));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Add Item - {{ invoice.invoice_number }}
            </h1>
            <InvoiceItemForm
                v-model:description="form.description"
                v-model:quantity="form.quantity"
                v-model:unit-price="form.unit_price"
                v-model:tax-rate="form.tax_rate"
                v-model:position="form.position"
                :invoice-id="invoice.id"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
