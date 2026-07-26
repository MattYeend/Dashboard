<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import PipelineForm from '@/pages/Pipelines/components/PipelineForm.vue';
import type { PipelineStatus } from '@/types';
import { store as pipelinesStore } from '@/routes/pipelines';

interface Props {
    statuses: PipelineStatus[];
}

defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    is_default: false,
    status_id: null as number | null,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        status_id: numberOrNull(data.status_id),
    })).post(pipelinesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Pipeline
            </h1>

            <PipelineForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:is-default="form.is_default"
                v-model:status-id="form.status_id"
                :statuses="statuses"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>