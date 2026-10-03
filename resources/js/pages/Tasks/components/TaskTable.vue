<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import { edit as tasksEdit, show as tasksShow } from '@/routes/tasks';
import type { Task } from '@/types';

defineProps<{
    tasks: Task[];
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
    { key: 'status', label: 'Status' },
    { key: 'assignee', label: 'Assigned To' },
    { key: 'due_date', label: 'Due Date' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="tasks"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No tasks found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <span class="font-medium text-gray-300">{{ row.title }}</span>
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

        <template #cell-assignee="{ row }">
            {{ row.assignee?.name ?? '-' }}
        </template>

        <template #cell-due_date="{ row }">
            {{ formatDate(row.due_date) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="tasksShow.url(row.id)"
                :edit-href="tasksEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
