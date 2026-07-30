<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DealStatus, Pipeline, PipelineStage } from '@/types';

interface DealPipelineFormData {
    pipeline_id: number | null;
    stage_id: number | null;
    status_id: number | null;
}

interface Props {
    pipelines: Pipeline[];
    pipelineStages: PipelineStage[];
    statuses: DealStatus[];
    errors: Partial<InertiaFormProps<DealPipelineFormData>['errors']>;
}

const props = defineProps<Props>();

const pipelineId = defineModel<number | null>('pipelineId', { default: null });
const stageId = defineModel<number | null>('stageId', { default: null });
const statusId = defineModel<number | null>('statusId', { default: null });

const availableStages = computed(() =>
    props.pipelineStages.filter(
        (stage) => stage.pipeline_id === pipelineId.value,
    ),
);
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="pipeline_id">Pipeline</Label>
            <Select v-model="pipelineId">
                <SelectTrigger id="pipeline_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select a pipeline" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="pipeline in pipelines"
                        :key="pipeline.id"
                        :value="pipeline.id"
                    >
                        {{ pipeline.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.pipeline_id" />
        </div>

        <div>
            <Label for="stage_id">Stage</Label>
            <Select v-model="stageId" :disabled="!pipelineId">
                <SelectTrigger id="stage_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select a stage" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="stage in availableStages"
                        :key="stage.id"
                        :value="stage.id"
                    >
                        {{ stage.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.stage_id" />
        </div>

        <div>
            <Label for="status_id">Status</Label>
            <Select v-model="statusId">
                <SelectTrigger id="status_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select a status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="status in statuses"
                        :key="status.id"
                        :value="status.id"
                    >
                        {{ status.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.status_id" />
        </div>
    </div>
</template>
