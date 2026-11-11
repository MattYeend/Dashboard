<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, update } from '@/routes/quote-statuses';
import type { QuoteStatus } from '@/types';

import QuoteStatusForm from './components/QuoteStatusForm.vue';
import type { QuoteStatusFormData } from '@/types/forms/quote-statuses';

const props = defineProps<{
    quoteStatus: QuoteStatus;
}>();

const form = useForm<QuoteStatusFormData>({
    title: props.quoteStatus.title,
    description: props.quoteStatus.description,
    background_colour: props.quoteStatus.background_colour,
    text_colour: props.quoteStatus.text_colour,
});

function submit(): void {
    form.put(update.url(props.quoteStatus.id));
}
</script>

<template>
    <Head :title="`Edit ${quoteStatus.title}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Quote statuses', href: index.url() },
            { title: quoteStatus.title, href: show.url(quoteStatus.id) },
            { title: 'Edit', href: '#' },
        ]"
    >
        <div class="mx-auto w-full max-w-3xl p-4">
            <h1 class="mb-6 text-2xl font-semibold">Edit quote status</h1>

            <QuoteStatusForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :is-system="quoteStatus.is_system"
                submit-label="Save changes"
                :cancel-href="show.url(quoteStatus.id)"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
