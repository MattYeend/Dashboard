<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import {
    edit as emailSuppressionsEdit,
    show as emailSuppressionsShow,
} from '@/routes/email-suppressions';
import type { EmailSuppression } from '@/types';

defineProps<{
    emailSuppressions: EmailSuppression[];
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
    { key: 'reason', label: 'Reason' },
    { key: 'campaign', label: 'Campaign' },
    { key: 'suppressed_at', label: 'Suppressed at' },
];

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="emailSuppressions"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No email suppressions found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-email="{ row }">
            <span class="font-medium text-gray-300">{{ row.email }}</span>
        </template>

        <template #cell-reason="{ row }">
            <span class="capitalize">{{ row.reason.replace(/_/g, ' ') }}</span>
        </template>

        <template #cell-campaign="{ row }">
            {{ row.campaign?.name ?? '-' }}
        </template>

        <template #cell-suppressed_at="{ row }">
            {{ formatDateTime(row.suppressed_at) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="emailSuppressionsShow.url(row.id)"
                :edit-href="emailSuppressionsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
