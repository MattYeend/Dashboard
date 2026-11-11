<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import {
    edit as quoteStatusesEdit,
    show as quoteStatusesShow,
} from '@/routes/quote-statuses';
import type { QuoteStatus } from '@/types';

defineProps<{
    quoteStatuses: QuoteStatus[];
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

const isSelectable = (row: QuoteStatus): boolean => !row.is_system;
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="quoteStatuses"
        :columns="columns"
        row-key="id"
        selectable
        :row-selectable="isSelectable"
        empty-message="No quote statuses found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <span class="font-medium text-gray-300">{{ row.title }}</span>
            <span
                v-if="row.is_system"
                class="ml-2 text-xs text-muted-foreground"
            >System</span>
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
                :show-href="quoteStatusesShow.url(row.id)"
                :edit-href="quoteStatusesEdit.url(row.id)"
                :can-delete="!row.is_system"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>