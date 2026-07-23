<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as invoiceStatusesStore } from '@/routes/invoice-statuses';
import InvoiceStatusForm from './components/InvoiceStatusForm.vue';
import type { InvoiceStatusFormData } from './components/InvoiceStatusForm.vue';

const form = useForm<InvoiceStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: InvoiceStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(invoiceStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Invoice Status
            </h1>
            <InvoiceStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Invoice Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
