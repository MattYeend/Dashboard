<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { index, store } from '@/routes/quote-statuses';

import QuoteStatusForm from './components/QuoteStatusForm.vue';
import type { QuoteStatusFormData } from '@/types/forms/quote-statuses';

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

    <AppLayout :breadcrumbs="[{ title: 'Quote statuses', href: index.url() }, { title: 'Create', href: '#' }]">
        <div class="mx-auto w-full max-w-3xl p-4">
            <h1 class="mb-6 text-2xl font-semibold">Create quote status</h1>

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
    </AppLayout>
</template>