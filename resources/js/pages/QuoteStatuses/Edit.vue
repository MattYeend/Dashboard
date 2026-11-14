<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteStatusForm from '@/pages/QuoteStatuses/components/QuoteStatusForm.vue';
import { show, update } from '@/routes/quote-statuses';
import type { QuoteStatus } from '@/types';
import type { QuoteStatusFormData } from '@/types/forms/quote-statuses-form-data';

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

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Quote Status</h1>

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
    </div>
</template>
