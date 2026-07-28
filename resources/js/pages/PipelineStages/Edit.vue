<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { update as pipelineStagesUpdate } from '@/routes/pipelines/stages';
import type { Pipeline, PipelineStage } from '@/types';
import PipelineStageForm from './components/PipelineStageForm.vue';

interface Props {
    pipeline: Pipeline;
    pipeline_stage: PipelineStage;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.pipeline_stage.title,
    description: props.pipeline_stage.description,
    position: props.pipeline_stage.position,
    background_colour: props.pipeline_stage.background_colour,
    text_colour: props.pipeline_stage.text_colour,
    is_won: props.pipeline_stage.is_won,
    is_lost: props.pipeline_stage.is_lost,
});

function submit(): void {
    form.put(
        pipelineStagesUpdate.url({
            pipeline: props.pipeline.id,
            stage: props.pipeline_stage.id,
        }),
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Stage - {{ pipeline.title }}
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
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>