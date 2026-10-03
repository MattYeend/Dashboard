<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate, truncate } from '@/lib/formatters';
import { edit as industriesEdit, show as industriesShow } from '@/routes/industries';
import type { Industry } from '@/types';

defineProps<{
    industries: Industry[];
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
    { key: 'code', label: 'SIC Code' },
    { key: 'description', label: 'Description' },
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="industries"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No industries found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <span class="font-medium text-gray-300">{{ truncate(row.title, 30) }}</span>
        </template>

        <template #cell-code="{ row }">
            {{ row.code ?? '-' }}
        </template>

        <template #cell-description="{ row }">
            {{ truncate(row.description, 30) }}
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="industriesShow.url(row.id)"
                :edit-href="industriesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
