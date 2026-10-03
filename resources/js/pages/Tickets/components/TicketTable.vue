<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as ticketsEdit, show as ticketsShow } from '@/routes/tickets';
import type { Ticket } from '@/types';

defineProps<{
    tickets: Ticket[];
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
    { key: 'priority', label: 'Priority' },
    { key: 'assignee', label: 'Assignee' },
    { key: 'resolved', label: 'Resolution' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="tickets"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No tickets found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
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

        <template #cell-priority="{ row }">
            <ColourBadge
                v-if="row.priority"
                :label="row.priority.title"
                :background-colour="row.priority.background_colour"
                :text-colour="row.priority.text_colour"
            />
            <span v-else>-</span>
        </template>

        <template #cell-assignee="{ row }">
            {{ row.assignee?.name ?? 'Unassigned' }}
        </template>

        <template #cell-resolved="{ row }">
            {{ row.resolved_at ? 'Resolved' : 'Unresolved' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="ticketsShow.url(row.id)"
                :edit-href="ticketsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
