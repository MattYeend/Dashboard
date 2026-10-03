<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate, formatMoney } from '@/lib/formatters';
import { edit as invoicesEdit, show as invoicesShow } from '@/routes/invoices';
import type { Invoice } from '@/types';

defineProps<{
    invoices: Invoice[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'invoice_number', label: 'Invoice Number' },
    { key: 'company', label: 'Company' },
    { key: 'status', label: 'Status' },
    { key: 'due_date', label: 'Due Date' },
    { key: 'total', label: 'Total' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="invoices"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No invoices found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-invoice_number="{ row }">
            <span class="font-medium text-gray-300">{{ row.invoice_number }}</span>
        </template>

        <template #cell-company="{ row }">
            {{ row.company?.name ?? '-' }}
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

        <template #cell-due_date="{ row }">
            {{ formatDate(row.due_date) }}
        </template>

        <template #cell-total="{ row }">
            {{ formatMoney(row.total, row.currency) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="invoicesShow.url(row.id)"
                :edit-href="invoicesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
