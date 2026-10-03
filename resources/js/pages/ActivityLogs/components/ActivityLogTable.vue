<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import { formatDateTime } from '@/lib/formatters';
import type { ActivityLog } from '@/types';

defineProps<{
    logs: ActivityLog[];
    canDelete: boolean;
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'action_label', label: 'Action' },
    { key: 'logged_in_user', label: 'Performed by' },
    { key: 'related_to_user', label: 'Related to' },
    { key: 'created_at', label: 'Date' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="logs"
        :columns="columns"
        row-key="id"
        :selectable="canDelete"
        empty-message="No activity logs found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-logged_in_user="{ row }">
            {{ row.logged_in_user?.name ?? 'System' }}
        </template>

        <template #cell-related_to_user="{ row }">
            {{ row.related_to_user?.name ?? 'N/A' }}
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDateTime(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <button
                v-if="canDelete"
                type="button"
                class="text-red-600 hover:text-red-900"
                @click="emit('delete', row.id)"
            >
                Delete
            </button>
        </template>
    </ResourceTable>
</template>
