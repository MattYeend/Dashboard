<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import InvoiceStatusBasicDetailsForm from './InvoiceStatusBasicDetailsForm.vue';
import InvoiceStatusColourForm from './InvoiceStatusColourForm.vue';
import { index as invoiceStatusesIndex } from '@/routes/invoice-statuses';

export interface InvoiceStatusFormData {
    title: string;
    description: string | null;
    background_colour: string;
    text_colour: string;
}

interface Errors {
    title?: string;
    description?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: InvoiceStatusFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: InvoiceStatusFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <InvoiceStatusBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <InvoiceStatusColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="invoiceStatusesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
