<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDateTime } from '@/lib/campaigns';
import {
    edit as campaignsEdit,
    show as campaignsShow,
} from '@/routes/campaigns';
import type { Campaign } from '@/types';
import CampaignStatusBadge from './CampaignStatusBadge.vue';

defineProps<{
    campaigns: Campaign[];
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
    { key: 'template', label: 'Email template' },
    { key: 'segment', label: 'Segment' },
    { key: 'owner', label: 'Owner' },
    { key: 'scheduled_at', label: 'Scheduled for' },
    { key: 'recipients_count', label: 'Recipients' },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="campaigns"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No campaigns found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-name="{ row }">
            <span class="font-medium text-gray-300">{{ row.name }}</span>
        </template>

        <template #cell-template="{ row }">
            {{ row.template?.name ?? '-' }}
        </template>

        <template #cell-segment="{ row }">
            {{ row.segment?.name ?? '-' }}
        </template>

        <template #cell-owner="{ row }">
            {{ row.owner?.name ?? '-' }}
        </template>

        <template #cell-scheduled_at="{ row }">
            {{ formatDateTime(row.scheduled_at, 'Not scheduled') }}
        </template>

        <template #cell-recipients_count="{ row }">
            {{ row.recipients_count ?? 0 }}
        </template>

        <template #cell-status="{ row }">
            <CampaignStatusBadge :status="row.status" />
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="campaignsShow.url(row.id)"
                :edit-href="campaignsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>