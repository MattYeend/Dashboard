<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { Invoice, InvoiceItem } from '@/types';
import InvoiceItemForm from './components/InvoiceItemForm.vue';
import { update as invoiceItemsUpdate } from '@/routes/invoices/items';

interface Props {
    invoice: Invoice;
    item: InvoiceItem;
}

const props = defineProps<Props>();

const form = useForm({
    description: props.item.description,
    quantity: props.item.quantity,
    unit_price: props.item.unit_price,
    tax_rate: Number(props.item.tax_rate),
    position: props.item.position,
});

function submit(): void {
    form.put(
        invoiceItemsUpdate.url({
            invoice: props.invoice.id,
            invoiceItem: props.item.id,
        }),
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Item — {{ invoice.invoice_number }}
            </h1>
            <InvoiceItemForm
                v-model:description="form.description"
                v-model:quantity="form.quantity"
                v-model:unit-price="form.unit_price"
                v-model:tax-rate="form.tax_rate"
                v-model:position="form.position"
                :invoice-id="invoice.id"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
