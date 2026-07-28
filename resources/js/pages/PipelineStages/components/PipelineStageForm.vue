<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import PipelineStageAppearanceForm from '@/pages/PipelineStages/components/PipelineStageAppearanceForm.vue';
import PipelineStageBasicDetailsForm from '@/pages/PipelineStages/components/PipelineStageBasicDetailsForm.vue';
import PipelineStageOutcomeForm from '@/pages/PipelineStages/components/PipelineStageOutcomeForm.vue';
import { index as pipelineStagesIndex } from '@/routes/pipelines/stages';

interface PipelineStageFormData {
    title: string;
    description: string | null;
    position: number;
    background_colour: string;
    text_colour: string;
    is_won: boolean;
    is_lost: boolean;
}

interface Props {
    pipelineId: number;
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<PipelineStageFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    required: true,
});
const position = defineModel<number>('position', { required: true });
const backgroundColour = defineModel<string>('backgroundColour', {
    required: true,
});
const textColour = defineModel<string>('textColour', { required: true });
const isWon = defineModel<boolean>('isWon', { required: true });
const isLost = defineModel<boolean>('isLost', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <PipelineStageBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            v-model:position="position"
            :errors="errors"
        />

        <PipelineStageAppearanceForm
            v-model:background-colour="backgroundColour"
            v-model:text-colour="textColour"
            :errors="errors"
        />

        <PipelineStageOutcomeForm
            v-model:is-won="isWon"
            v-model:is-lost="isLost"
            :errors="errors"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="pipelineStagesIndex.url({ pipeline: pipelineId })">
                    Cancel
                </Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Stage' : 'Create Stage' }}
            </Button>
        </div>
    </form>
</template>
