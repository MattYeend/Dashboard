<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteItemForm from '@/pages/QuoteItems/components/QuoteItemForm.vue';
import { store as quoteItemsStore } from '@/routes/quotes/items';
import type { Quote } from '@/types';
import type { ProductOption, QuoteItemFormData } from '@/types/forms';

const props = defineProps<{
    quote: Quote;
    products: ProductOption[];
    units: string[];
}>();

const form = useForm<QuoteItemFormData>({
    product_id: null,
    name: '',
    description: null,
    unit: props.units.includes('each') ? 'each' : (props.units[0] ?? ''),
    quantity: '1.00',
    unit_price: '0.00',
    discount_percent: '0.00',
    tax_rate: '20.00',
});

function submit(): void {
    form.post(quoteItemsStore.url({ quote: props.quote.id }));
}
</script>

<template>
    <Head :title="`Add item - ${quote.quote_number}`" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Add Item - {{ quote.quote_number }}
            </h1>

            <QuoteItemForm
                v-model="form"
                :quote-id="quote.id"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                :products="products"
                :units="units"
                @submit="submit"
            />
        </div>
    </div>
</template>