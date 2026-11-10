<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as productsEdit, show as productsShow } from '@/routes/products';
import type { Product } from '@/types';

defineProps<{
    products: Product[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'sku', label: 'SKU' },
    { key: 'name', label: 'Name' },
    { key: 'unit', label: 'Unit' },
    { key: 'formatted_unit_price', label: 'Unit Price' },
    { key: 'tax_rate', label: 'Tax Rate' },
    { key: 'is_active', label: 'Active' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="products"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No products found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-tax_rate="{ row }">
            {{ row.tax_rate }}%
        </template>

        <template #cell-is_active="{ row }">
            {{ row.is_active ? 'Yes' : 'No' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="productsShow.url(row.id)"
                :edit-href="productsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>