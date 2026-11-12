<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import QuoteStatusBadge from '@/pages/Quotes/components/QuoteStatusBadge.vue';
import { edit as quotesEdit, show as quotesShow } from '@/routes/quotes';
import type { Quote } from '@/types';

defineProps<{
    quotes: Quote[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const LOCKED_STATUS_KEYS = ['sent', 'accepted', 'converted'];

const columns: ResourceTableColumn[] = [
    { key: 'quote_number', label: 'Quote Number' },
    { key: 'title', label: 'Title' },
    { key: 'quotable_name', label: 'Customer' },
    { key: 'status', label: 'Status' },
    { key: 'formatted_total', label: 'Total' },
    { key: 'valid_until', label: 'Valid Until' },
];

/**
 * Hide the delete button for locked statuses. The server enforces this
 * regardless, so this is a usability check only.
 */
function canDelete(quote: Quote): boolean {
    const key = quote.status?.system_key;

    return !key || !LOCKED_STATUS_KEYS.includes(key);
}
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="quotes"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No quotes found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-status="{ row }">
            <QuoteStatusBadge :status="row.status" />
        </template>

        <template #cell-valid_until="{ row }">
            {{ formatDate(row.valid_until) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="quotesShow.url(row.id)"
                :edit-href="quotesEdit.url(row.id)"
                :can-delete="canDelete(row)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>