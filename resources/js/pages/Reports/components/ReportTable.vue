<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as reportsEdit, show as reportsShow } from '@/routes/reports';
import type { Report } from '@/types';

defineProps<{
    reports: Report[];
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
    { key: 'type_label', label: 'Type' },
    { key: 'format', label: 'Format' },
    { key: 'is_scheduled', label: 'Scheduled' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="reports"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No reports found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-is_scheduled="{ row }">
            {{ row.is_scheduled ? 'Yes' : 'No' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="reportsShow.url(row.id)"
                :edit-href="reportsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
