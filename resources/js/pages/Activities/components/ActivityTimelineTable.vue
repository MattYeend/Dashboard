<script setup lang="ts">
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import { formatDateTime, truncate } from '@/lib/formatters';
import ActivityItem from '@/pages/Activities/components/ActivityItem.vue';
import type { Activity } from '@/types';

defineProps<{
    activities: Activity[];
    loading: boolean;
}>();

const columns: ResourceTableColumn[] = [
    { key: 'type', label: 'Type' },
    { key: 'description', label: 'Description' },
    { key: 'occurred_at', label: 'Occurred at' },
    { key: 'creator', label: 'Logged by' },
];
</script>

<template>
    <ResourceTable
        :rows="activities"
        :columns="columns"
        row-key="id"
        :empty-message="loading ? 'Loading...' : 'No activity yet.'"
        class="text-xs [&_table]:table-fixed [&_td]:py-1.5 [&_td]:break-words [&_td]:whitespace-normal [&_th]:py-1.5"
    >
        <template #cell-type="{ row }">
            <ActivityItem :activity="row" />
        </template>
        <template #cell-description="{ row }">
            <span :title="row.description ?? undefined">{{
                truncate(row.description)
            }}</span>
        </template>
        <template #cell-occurred_at="{ row }">
            {{ formatDateTime(row.occurred_at) }}
        </template>
        <template #cell-creator="{ row }">
            {{ row.creator?.name ?? 'System' }}
        </template>
    </ResourceTable>
</template>
