<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ColourBadge from '@/components/ColourBadge.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import {
    edit as taskStatusesEdit,
    show as taskStatusesShow,
} from '@/routes/task-statuses';
import type { TaskStatus } from '@/types';

defineProps<{
    taskStatuses: TaskStatus[];
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
    { key: 'description', label: 'Description' },
    { key: 'preview', label: 'Preview' },
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="taskStatuses"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No task statuses found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <span class="font-medium text-gray-300">{{ row.title }}</span>
        </template>

        <template #cell-description="{ row }">
            {{ row.description ?? '-' }}
        </template>

        <template #cell-preview="{ row }">
            <ColourBadge
                :label="row.title"
                :background-colour="row.background_colour"
                :text-colour="row.text_colour"
            />
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="taskStatusesShow.url(row.id)"
                :edit-href="taskStatusesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
