<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDateTime, humaniseStatus } from '@/lib/campaign-recipients';
import { show as campaignRecipientsShow } from '@/routes/campaign-recipients';
import type { CampaignRecipient } from '@/types';

defineProps<{
    campaignRecipients: CampaignRecipient[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'email', label: 'Email' },
    { key: 'name', label: 'Name' },
    { key: 'campaign', label: 'Campaign' },
    { key: 'status', label: 'Status' },
    { key: 'sent_at', label: 'Sent' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="campaignRecipients"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No campaign recipients found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-email="{ row }">
            <span class="font-medium text-gray-300">{{ row.email }}</span>
        </template>

        <template #cell-name="{ row }">
            {{ row.name ?? '-' }}
        </template>

        <template #cell-campaign="{ row }">
            {{ row.campaign?.name ?? '-' }}
        </template>

        <template #cell-status="{ row }">
            {{ humaniseStatus(row.status) }}
        </template>

        <template #cell-sent_at="{ row }">
            {{ formatDateTime(row.sent_at, 'Not sent') }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="campaignRecipientsShow.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>