<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as contactsEdit, show as contactsShow } from '@/routes/contacts';
import type { Contact } from '@/types';

defineProps<{
    contacts: Contact[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'contactable_type_label', label: 'Type' },
    { key: 'contactable_name', label: 'Contact Of' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'city', label: 'City' },
    { key: 'country', label: 'Country' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="contacts"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No contacts found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="contactsShow.url(row.id)"
                :edit-href="contactsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
