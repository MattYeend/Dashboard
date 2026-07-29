<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import DealStatusForm from './components/DealStatusForm.vue';
import type { DealStatusFormData } from './components/DealStatusForm.vue';
import { store as dealStatusesStore } from '@/routes/deal-statuses';

const form = useForm<DealStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: DealStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(dealStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Deal Status
            </h1>
            <DealStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Order Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
