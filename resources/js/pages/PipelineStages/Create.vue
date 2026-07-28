<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as pipelineStagesStore } from '@/routes/pipelines/stages';
import type { Pipeline } from '@/types';
import PipelineStageForm from './components/PipelineStageForm.vue';

interface Props {
    pipeline: Pipeline;
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    description: null as string | null,
    position: 0,
    background_colour: '#e5e7eb',
    text_colour: '#111827',
    is_won: false,
    is_lost: false,
});

function submit(): void {
    form.post(pipelineStagesStore.url({ pipeline: props.pipeline.id }));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Add Stage - {{ pipeline.title }}
            </h1>
            <PipelineStageForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:position="form.position"
                v-model:background-colour="form.background_colour"
                v-model:text-colour="form.text_colour"
                v-model:is-won="form.is_won"
                v-model:is-lost="form.is_lost"
                :pipeline-id="pipeline.id"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>