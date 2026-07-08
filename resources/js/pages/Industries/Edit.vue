<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as industriesUpdate } from '@/routes/industries';
import type { Industry } from '@/types';
import IndustryForm from './components/IndustryForm.vue';
import type { IndustryFormData } from './components/IndustryForm.vue';

const props = defineProps<{
    industry: Industry;
}>();

const form = useForm<IndustryFormData>({
    title: props.industry.title,
    code: props.industry.code,
    description: props.industry.description,
});

function onFormUpdate(updated: IndustryFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        code: nullIfBlank(data.code),
        description: nullIfBlank(data.description),
    })).put(industriesUpdate.url(props.industry.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Industry
            </h1>
            <IndustryForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Industry"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
