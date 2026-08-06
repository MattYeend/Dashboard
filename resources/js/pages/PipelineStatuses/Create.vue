<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as pipelineStatusesStore } from '@/routes/pipeline-statuses';
import PipelineStatusForm from './components/PipelineStatusForm.vue';
import type { PipelineStatusFormData } from './components/PipelineStatusForm.vue';

const form = useForm<PipelineStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: PipelineStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(pipelineStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Pipeline Status
            </h1>
            <PipelineStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Pipeline Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
