<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteForm from '@/pages/Quotes/components/QuoteForm.vue';
import { index, store } from '@/routes/quotes';
import type { QuoteFormData, SelectOption } from '@/types/forms/quotes-form-data';

defineProps<{
    quotableTypes: SelectOption[];
    currencies: string[];
}>();

const form = useForm<QuoteFormData>({
    quotable_type: '',
    quotable_id: null,
    title: '',
    description: null,
    notes: null,
    terms: null,
    subtotal: '0.00',
    discount_amount: '0.00',
    tax_amount: '0.00',
    currency: 'GBP',
    issued_at: null,
    valid_until: null,
});

function submit(): void {
    form.post(store.url());
}
</script>

<template>
    <Head title="Create quote" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Quote</h1>

            <QuoteForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :quotable-types="quotableTypes"
                :currencies="currencies"
                submit-label="Create quote"
                :cancel-href="index.url()"
                @submit="submit"
            />
        </div>
    </div>
</template>
