<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as pipelineStagesEdit, show as pipelineStagesShow } from '@/routes/pipelines/stages';
import type { PipelineStage } from '@/types';

defineProps<{
    pipelineStages: PipelineStage[];
    pipelineId: number;
    trashedOnly: boolean;
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    restore: [id: number];
    forceDelete: [id: number];
    bulkDelete: [ids: Array<number | string>];
    bulkRestore: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'position', label: 'Position' },
    { key: 'title', label: 'Title' },
    { key: 'is_won', label: 'Won' },
    { key: 'is_lost', label: 'Lost' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="pipelineStages"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No pipeline stages found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton
                v-if="!trashedOnly"
                @click="emit('bulkDelete', selection)"
            >
                Delete selected
            </BulkActionButton>
            <BulkActionButton
                v-else
                variant="neutral"
                @click="emit('bulkRestore', selection)"
            >
                Restore selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <ColourBadge
                :label="row.title"
                :background-colour="row.background_colour"
                :text-colour="row.text_colour"
            />
        </template>

        <template #cell-is_won="{ row }">
            {{ row.is_won ? 'Yes' : 'No' }}
        </template>

        <template #cell-is_lost="{ row }">
            {{ row.is_lost ? 'Yes' : 'No' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="pipelineStagesShow.url({ pipeline: pipelineId, stage: row.id })"
                :edit-href="pipelineStagesEdit.url({ pipeline: pipelineId, stage: row.id })"
                :trashed="Boolean(row.deleted_at)"
                @delete="emit('delete', row.id)"
                @restore="emit('restore', row.id)"
                @force-delete="emit('forceDelete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
