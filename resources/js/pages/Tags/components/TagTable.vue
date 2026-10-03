<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import { edit as tagsEdit, show as tagsShow } from '@/routes/tags';
import type { Tag } from '@/types';

defineProps<{
    tags: Tag[];
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
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="tags"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No tags found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-name="{ row }">
            <span class="font-medium text-gray-300">{{ row.name }}</span>
        </template>

        <template #cell-slug="{ row }">
            {{ row.slug }}
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="tagsShow.url(row.id)"
                :edit-href="tagsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
