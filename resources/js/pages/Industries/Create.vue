<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as industriesStore } from '@/routes/industries';
import IndustryForm from './components/IndustryForm.vue';
import type { IndustryFormData } from './components/IndustryForm.vue';

const form = useForm<IndustryFormData>({
    title: '',
    code: null,
    description: null,
});

function onFormUpdate(updated: IndustryFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        code: nullIfBlank(data.code),
        description: nullIfBlank(data.description),
    })).post(industriesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Industry
            </h1>
            <IndustryForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Industry"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
