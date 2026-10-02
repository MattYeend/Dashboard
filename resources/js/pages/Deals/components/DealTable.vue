<script setup lang="ts">
import ColourBadge from '@/components/ColourBadge.vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as dealsEdit, show as dealsShow } from '@/routes/deals';
import type { Deal } from '@/types';

defineProps<{
    deals: Deal[];
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
    { key: 'stage', label: 'Stage' },
    { key: 'status', label: 'Status' },
    { key: 'value', label: 'Value' },
    { key: 'company', label: 'Company' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="deals"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No deals found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-stage="{ row }">
            <ColourBadge
                v-if="row.stage"
                :label="row.stage.title"
                :background-colour="row.stage.background_colour"
                :text-colour="row.stage.text_colour"
            />
            <span v-else>-</span>
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

        <template #cell-value="{ row }">
            {{ row.currency }} {{ row.value }}
        </template>

        <template #cell-company="{ row }">
            {{ row.company?.name ?? '-' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="dealsShow.url(row.id)"
                :edit-href="dealsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
