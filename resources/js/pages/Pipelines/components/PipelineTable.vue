<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as pipelinesEdit, show as pipelinesShow } from '@/routes/pipelines';
import type { Pipeline } from '@/types';

defineProps<{
    pipelines: Pipeline[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'is_default', label: 'Default' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="pipelines"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No pipelines found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-is_default="{ row }">
            {{ row.is_default ? 'Yes' : 'No' }}
        </template>

        <template #cell-status="{ row }">
            <ColourBadge
                v-if="row.status"
                :label="row.status.title"
                :background-colour="row.status.background_colour"
                :text-colour="row.status.text_colour"
            />
            <span v-else>-</span>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="pipelinesShow.url(row.id)"
                :edit-href="pipelinesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
