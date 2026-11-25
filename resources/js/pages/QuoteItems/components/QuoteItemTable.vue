<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDecimalMoney } from '@/lib/decimalMoney';
import {
    edit as quoteItemsEdit,
    show as quoteItemsShow,
} from '@/routes/quotes/items';
import type { QuoteItem } from '@/types';

defineProps<{
    items: QuoteItem[];
    quoteId: number;
    currency: string;
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
    { key: 'name', label: 'Item' },
    { key: 'quantity', label: 'Quantity' },
    { key: 'unit_price', label: 'Unit Price' },
    { key: 'discount_percent', label: 'Discount' },
    { key: 'tax_rate', label: 'Tax Rate' },
    { key: 'line_total', label: 'Line Total' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="items"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No quote items found."
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

        <template #cell-name="{ row }">
            <div class="font-medium text-gray-300">{{ row.name }}</div>
            <div v-if="row.product" class="text-xs text-gray-400">
                {{ row.product.sku }}
            </div>
        </template>

        <template #cell-quantity="{ row }">
            {{ row.quantity }} {{ row.unit }}
        </template>

        <template #cell-unit_price="{ row }">
            {{ formatDecimalMoney(row.unit_price, currency) }}
        </template>

        <template #cell-discount_percent="{ row }">
            {{ row.discount_percent }}%
        </template>

        <template #cell-tax_rate="{ row }"> {{ row.tax_rate }}% </template>

        <template #cell-line_total="{ row }">
            {{ formatDecimalMoney(row.line_total, currency) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="
                    quoteItemsShow.url({ quote: quoteId, quoteItem: row.id })
                "
                :edit-href="
                    quoteItemsEdit.url({ quote: quoteId, quoteItem: row.id })
                "
                :trashed="Boolean(row.deleted_at)"
                @delete="emit('delete', row.id)"
                @restore="emit('restore', row.id)"
                @force-delete="emit('forceDelete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
