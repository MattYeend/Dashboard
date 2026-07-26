<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import type { PipelineStatus } from '@/types';
import PipelineStatusForm from './components/PipelineStatusForm.vue';
import type { PipelineStatusFormData } from './components/PipelineStatusForm.vue';
import { update as pipelineStatusesUpdate } from '@/routes/pipeline-statuses';

const props = defineProps<{
    pipelineStatus: PipelineStatus;
}>();

const form = useForm<PipelineStatusFormData>({
    title: props.pipelineStatus.title,
    description: props.pipelineStatus.description,
    background_colour: props.pipelineStatus.background_colour,
    text_colour: props.pipelineStatus.text_colour,
});

function onFormUpdate(updated: PipelineStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).put(pipelineStatusesUpdate.url(props.pipelineStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Pipeline Status
            </h1>
            <PipelineStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Pipeline Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>