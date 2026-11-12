<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteForm from '@/pages/Quotes/components/QuoteForm.vue';
import { show, update } from '@/routes/quotes';
import type { Quote } from '@/types';
import type { QuoteFormData, SelectOption } from '@/types/forms/quotes';

const props = defineProps<{
    quote: Quote;
    quotableTypes: SelectOption[];
    currencies: string[];
}>();

const form = useForm<QuoteFormData>({
    quotable_type: props.quote.quotable_type_key ?? '',
    quotable_id: props.quote.quotable_id,
    title: props.quote.title,
    description: props.quote.description,
    notes: props.quote.notes,
    terms: props.quote.terms,
    subtotal: props.quote.subtotal,
    discount_amount: props.quote.discount_amount,
    tax_amount: props.quote.tax_amount,
    currency: props.quote.currency,
    issued_at: props.quote.issued_at,
    valid_until: props.quote.valid_until,
});

function submit(): void {
    form.put(update.url(props.quote.id));
}
</script>

<template>
    <Head :title="`Edit ${quote.quote_number}`" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Quote</h1>

            <QuoteForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :quotable-types="quotableTypes"
                :currencies="currencies"
                submit-label="Save changes"
                :cancel-href="show.url(quote.id)"
                @submit="submit"
            />
        </div>
    </div>
</template>