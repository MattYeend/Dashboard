<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import {
    edit as invoiceStatusesEdit,
    show as invoiceStatusesShow,
} from '@/routes/invoice-statuses';
import type { InvoiceStatus } from '@/types';

defineProps<{
    invoiceStatuses: InvoiceStatus[];
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
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="invoiceStatuses"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No invoice statuses found."
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

        <template #actions="{ row }">
            <RowActions
                :show-href="invoiceStatusesShow.url(row.id)"
                :edit-href="invoiceStatusesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
