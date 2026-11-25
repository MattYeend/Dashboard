<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as emailSuppressionsStore } from '@/routes/email-suppressions';
import type { SelectOption } from '@/types';
import type { EmailSuppressionFormData } from '@/types/forms';
import EmailSuppressionForm from './components/EmailSuppressionForm.vue';

defineProps<{
    reasonOptions: SelectOption[];
}>();

const form = useForm<EmailSuppressionFormData>({
    email: '',
    reason: 'manual',
    notes: null,
});

function onFormUpdate(updated: EmailSuppressionFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.post(emailSuppressionsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Email Suppression
            </h1>
            <EmailSuppressionForm
                :form="form"
                :errors="form.errors"
                :reason-options="reasonOptions"
                submit-label="Create Email Suppression"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
