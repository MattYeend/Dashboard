<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteItemForm from '@/pages/QuoteItems/components/QuoteItemForm.vue';
import { update as quoteItemsUpdate } from '@/routes/quotes/items';
import type { Quote, QuoteItem } from '@/types';
import type { ProductOption, QuoteItemFormData } from '@/types/forms';

const props = defineProps<{
    quote: Quote;
    item: QuoteItem;
    products: ProductOption[];
    units: string[];
}>();

const form = useForm<QuoteItemFormData>({
    product_id: props.item.product_id,
    name: props.item.name,
    description: props.item.description,
    unit: props.item.unit,
    quantity: props.item.quantity,
    unit_price: props.item.unit_price,
    discount_percent: props.item.discount_percent,
    tax_rate: props.item.tax_rate,
});

function submit(): void {
    form.put(
        quoteItemsUpdate.url({
            quote: props.quote.id,
            quoteItem: props.item.id,
        }),
    );
}
</script>

<template>
    <Head :title="`Edit item - ${quote.quote_number}`" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Item - {{ quote.quote_number }}
            </h1>

            <QuoteItemForm
                v-model="form"
                :quote-id="quote.id"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                :products="products"
                :units="units"
                @submit="submit"
            />
        </div>
    </div>
</template>