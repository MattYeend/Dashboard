<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as invoiceStatusesUpdate } from '@/routes/invoice-statuses';
import type { InvoiceStatus } from '@/types';
import InvoiceStatusForm from './components/InvoiceStatusForm.vue';
import type { InvoiceStatusFormData } from './components/InvoiceStatusForm.vue';

const props = defineProps<{
    invoiceStatus: InvoiceStatus;
}>();

const form = useForm<InvoiceStatusFormData>({
    title: props.invoiceStatus.title,
    description: props.invoiceStatus.description,
    background_colour: props.invoiceStatus.background_colour,
    text_colour: props.invoiceStatus.text_colour,
});

function onFormUpdate(updated: InvoiceStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).put(invoiceStatusesUpdate.url(props.invoiceStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Invoice Status
            </h1>
            <InvoiceStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Invoice Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
