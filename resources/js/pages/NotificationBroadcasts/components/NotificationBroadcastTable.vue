<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import { edit as notificationBroadcastsEdit } from '@/routes/notification-broadcasts';
import type { NotificationBroadcast } from '@/types';

defineProps<{
    notificationBroadcasts: NotificationBroadcast[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    send: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'audience_type', label: 'Audience' },
    { key: 'sent_at', label: 'Sent' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="notificationBroadcasts"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No notifications found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <span class="font-medium text-gray-300">{{ row.title }}</span>
        </template>

        <template #cell-audience_type="{ row }">
            {{ row.audience_type }}
        </template>

        <template #cell-sent_at="{ row }">
            <span
                v-if="row.sent_at"
                class="rounded border border-green-600 px-2 py-0.5 text-xs font-medium text-green-600"
            >
                Sent {{ formatDate(row.sent_at) }}
            </span>
            <span
                v-else
                class="rounded border border-gray-500 px-2 py-0.5 text-xs font-medium text-gray-500"
            >
                Not sent
            </span>
        </template>

        <template #actions="{ row }">
            <RowActions
                :edit-href="row.sent_at ? undefined : notificationBroadcastsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            >
                <button
                    v-if="!row.sent_at"
                    type="button"
                    class="text-blue-600 hover:text-blue-900"
                    @click="emit('send', row.id)"
                >
                    Send
                </button>
            </RowActions>
        </template>
    </ResourceTable>
</template>
