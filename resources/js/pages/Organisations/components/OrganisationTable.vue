<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import {
    edit as organisationsEdit,
    show as organisationsShow,
} from '@/routes/organisations';
import type { Organisation } from '@/types';

defineProps<{
    organisations: Organisation[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug' },
    { key: 'members_count', label: 'Members' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="organisations"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No organisations found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="organisationsShow.url(row.id)"
                :edit-href="organisationsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
