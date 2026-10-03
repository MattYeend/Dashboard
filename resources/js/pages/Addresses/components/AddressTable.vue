<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as addressesEdit, show as addressesShow } from '@/routes/addresses';
import type { Address } from '@/types';

defineProps<{
    addresses: Address[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'addressable_type_label', label: 'Type' },
    { key: 'addressable_name', label: 'Owner' },
    { key: 'address_line_one', label: 'Address' },
    { key: 'city', label: 'City' },
    { key: 'postcode', label: 'Postcode' },
    { key: 'country', label: 'Country' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="addresses"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No addresses found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="addressesShow.url(row.id)"
                :edit-href="addressesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
