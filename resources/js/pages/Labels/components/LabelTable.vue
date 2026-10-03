<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as labelsEdit, show as labelsShow } from '@/routes/labels';
import type { Label } from '@/types';

defineProps<{
    labels: Label[];
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
    { key: 'preview', label: 'Preview' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="labels"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No labels found."
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
            <span class="font-mono text-xs text-gray-400">{{ row.slug }}</span>
        </template>

        <template #cell-preview="{ row }">
            <ColourBadge
                :label="row.name"
                :background-colour="row.background_colour"
                :text-colour="row.text_colour"
            />
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="labelsShow.url(row.id)"
                :edit-href="labelsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
