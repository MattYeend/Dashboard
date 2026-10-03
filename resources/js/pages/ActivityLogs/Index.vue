<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import activityLogs, { index, destroy } from '@/routes/activity-logs';
import type {
    ActivityLog,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import ActivityLogTable from './components/ActivityLogTable.vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Activity Logs', href: index().url }],
    },
});

interface Props {
    logs: {
        data: ActivityLog[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta & {
        can_view_any: boolean;
        can_export: boolean;
        can_delete: boolean;
    };
    sort_fields: Record<string, string>;
    action_options: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => index().url,
    searchPlaceholder: 'Search activity logs…',
    sortFields: () => props.sort_fields,
    extraFilters: [
        {
            key: 'action',
            type: 'select',
            options: () => [
                { value: '', label: 'All actions' },
                ...Object.entries(props.action_options).map(
                    ([value, label]) => ({ value, label }),
                ),
            ],
        },
        {
            key: 'date_from',
            type: 'text',
            placeholder: 'From (YYYY-MM-DD)',
        },
        {
            key: 'date_to',
            type: 'text',
            placeholder: 'To (YYYY-MM-DD)',
        },
    ],
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(destroy(id).url, options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(activityLogs.bulk.delete().url, { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Activity Logs" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Activity Logs"
                :can-create="false"
                :export-href="activityLogs.export({ query: filters }).url"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ActivityLogTable
                v-model:selected="selectedIds"
                :logs="logs.data"
                :can-delete="permissions_meta.can_delete"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="logs.meta"
                :links="logs.links"
                resource-label="activity logs"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete activity log"
            description="This log entry will be permanently deleted. This cannot be undone."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete selected activity logs"
            :description="`${bulkDeleteAction.ids.length} log entry(ies) will be permanently deleted. This cannot be undone.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
