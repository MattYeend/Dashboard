<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import { edit as ordersEdit, show as ordersShow } from '@/routes/orders';
import type { Order } from '@/types';

defineProps<{
    orders: Order[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'order_number', label: 'Order Number' },
    { key: 'title', label: 'Title' },
    { key: 'orderable_type_label', label: 'Type' },
    { key: 'orderable_name', label: 'Order Of' },
    { key: 'total_amount', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'ordered_at', label: 'Ordered At' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="orders"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No orders found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-ordered_at="{ row }">
            {{ formatDate(row.ordered_at) }}
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

        <template #actions="{ row }">
            <RowActions
                :show-href="ordersShow.url(row.id)"
                :edit-href="ordersEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
