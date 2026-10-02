<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatMoney } from '@/lib/formatters';
import {
    edit as invoiceItemsEdit,
    show as invoiceItemsShow,
} from '@/routes/invoices/items';
import type { InvoiceItem } from '@/types';

defineProps<{
    items: InvoiceItem[];
    invoiceId: number;
    trashedOnly: boolean;
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    restore: [id: number];
    forceDelete: [id: number];
    bulkDelete: [ids: Array<number | string>];
    bulkRestore: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'description', label: 'Description' },
    { key: 'quantity', label: 'Quantity' },
    { key: 'unit_price', label: 'Unit Price' },
    { key: 'tax_rate', label: 'Tax Rate' },
    { key: 'total', label: 'Total' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="items"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No invoice items found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton
                v-if="!trashedOnly"
                @click="emit('bulkDelete', selection)"
            >
                Delete selected
            </BulkActionButton>
            <BulkActionButton
                v-else
                variant="neutral"
                @click="emit('bulkRestore', selection)"
            >
                Restore selected
            </BulkActionButton>
        </template>

        <template #cell-description="{ row }">
            <span class="font-medium text-gray-300">{{ row.description }}</span>
        </template>

        <template #cell-unit_price="{ row }">
            {{ formatMoney(row.unit_price) }}
        </template>

        <template #cell-tax_rate="{ row }"> {{ row.tax_rate }}% </template>

        <template #cell-total="{ row }">
            {{ formatMoney(row.total) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="
                    invoiceItemsShow.url({
                        invoice: invoiceId,
                        invoiceItem: row.id,
                    })
                "
                :edit-href="
                    invoiceItemsEdit.url({
                        invoice: invoiceId,
                        invoiceItem: row.id,
                    })
                "
                :trashed="Boolean(row.deleted_at)"
                @delete="emit('delete', row.id)"
                @restore="emit('restore', row.id)"
                @force-delete="emit('forceDelete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
