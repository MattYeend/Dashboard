<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import PipelineForm from '@/pages/Pipelines/components/PipelineForm.vue';
import { update as pipelinesUpdate } from '@/routes/pipelines';
import type { Pipeline, PipelineStatus } from '@/types';

interface Props {
    pipeline: Pipeline;
    statuses: PipelineStatus[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.pipeline.title,
    description: props.pipeline.description ?? '',
    is_default: props.pipeline.is_default,
    status_id: props.pipeline.status_id,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        status_id: numberOrNull(data.status_id),
    })).put(pipelinesUpdate.url(props.pipeline.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Pipeline
            </h1>

            <PipelineForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:is-default="form.is_default"
                v-model:status-id="form.status_id"
                :statuses="statuses"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
