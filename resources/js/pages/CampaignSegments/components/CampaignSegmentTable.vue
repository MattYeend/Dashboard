<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import {
    edit as campaignSegmentsEdit,
    show as campaignSegmentsShow,
} from '@/routes/campaign-segments';
import type { CampaignSegment } from '@/types';
 
defineProps<{
    campaignSegments: CampaignSegment[];
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
    { key: 'description', label: 'Description' },
    { key: 'criteria', label: 'Criteria' },
    { key: 'estimated_recipients_count', label: 'Estimated recipients' },
    { key: 'last_refreshed_at', label: 'Last refreshed' },
];
 
function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Never';
    }
 
    return new Date(value).toLocaleString();
}
</script>
 
<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="campaignSegments"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No campaign segments found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>
 
        <template #cell-name="{ row }">
            <span class="font-medium text-gray-300">{{ row.name }}</span>
        </template>
 
        <template #cell-description="{ row }">
            {{ row.description ?? '-' }}
        </template>
 
        <template #cell-criteria="{ row }">
            {{ row.criteria?.length ?? 0 }}
        </template>
 
        <template #cell-estimated_recipients_count="{ row }">
            {{ row.estimated_recipients_count ?? 0 }}
        </template>
 
        <template #cell-last_refreshed_at="{ row }">
            {{ formatDateTime(row.last_refreshed_at) }}
        </template>
 
        <template #actions="{ row }">
            <RowActions
                :show-href="campaignSegmentsShow.url(row.id)"
                :edit-href="campaignSegmentsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>