<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import PipelineBasicDetailsForm from '@/pages/Pipelines/components/PipelineBasicDetailsForm.vue';
import { index as pipelinesIndex } from '@/routes/pipelines';
import type { PipelineStatus, UserOption } from '@/types';

interface PipelineFormData {
    title: string;
    description: string | null;
    is_default: boolean;
    status_id: number | null;
    assigned_to: number | null;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<PipelineFormData>['errors']>;
    statuses: PipelineStatus[];
    users: UserOption[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const isDefault = defineModel<boolean>('isDefault', { required: true });
const statusId = defineModel<number | null>('statusId', { default: null });
const assignedTo = defineModel<number | null>('assignedTo', { default: null });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <PipelineBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            v-model:is-default="isDefault"
            v-model:status-id="statusId"
            v-model:assigned-to="assignedTo"
            :statuses="statuses"
            :users="users"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="pipelinesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Pipeline' : 'Create Pipeline' }}
            </Button>
        </div>
    </form>
</template>
