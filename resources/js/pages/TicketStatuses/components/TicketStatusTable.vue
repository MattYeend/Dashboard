<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import {
    edit as ticketStatusesEdit,
    show as ticketStatusesShow,
} from '@/routes/ticket-statuses';
import type { TicketStatus } from '@/types';

defineProps<{
    ticketStatuses: TicketStatus[];
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
        :rows="ticketStatuses"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No ticket statuses found."
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
                :show-href="ticketStatusesShow.url(row.id)"
                :edit-href="ticketStatusesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
