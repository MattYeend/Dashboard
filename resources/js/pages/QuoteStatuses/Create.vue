<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteStatusForm from '@/pages/QuoteStatuses/components/QuoteStatusForm.vue';
import { index, store } from '@/routes/quote-statuses';
import type { QuoteStatusFormData } from '@/types/forms';

const form = useForm<QuoteStatusFormData>({
    title: '',
    description: null,
    background_colour: '#e2e8f0',
    text_colour: '#1a202c',
});

function submit(): void {
    form.post(store.url());
}
</script>

<template>
    <Head title="Create quote status" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Quote Status</h1>

            <QuoteStatusForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :is-system="false"
                submit-label="Create quote status"
                :cancel-href="index.url()"
                @submit="submit"
            />
        </div>
    </div>
</template>
