<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import activityLogs, { index, destroy } from '@/routes/activity-logs';
import type {
    ActivityLog,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';

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

const urlParams = new URLSearchParams(window.location.search);

const filters = ref({
    search: urlParams.get('search') ?? '',
    action: urlParams.get('action') ?? '',
    date_from: urlParams.get('date_from') ?? '',
    date_to: urlParams.get('date_to') ?? '',
    sort_by: urlParams.get('sort_by') ?? 'created_at',
    sort_direction: urlParams.get('sort_direction') ?? 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteDialogOpen = ref(false);
const selectedLogId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'action_label', label: 'Action' },
    { key: 'logged_in_user', label: 'Performed by' },
    { key: 'related_to_user', label: 'Related to' },
    { key: 'created_at', label: 'Date' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search activity logs…',
    },
    {
        key: 'action',
        type: 'select' as const,
        get options() {
            return [
                { value: '', label: 'All actions' },
                ...Object.entries(props.action_options).map(
                    ([value, label]) => ({ value, label }),
                ),
            ];
        },
    },
    {
        key: 'date_from',
        type: 'text' as const,
        placeholder: 'From (YYYY-MM-DD)',
    },
    {
        key: 'date_to',
        type: 'text' as const,
        placeholder: 'To (YYYY-MM-DD)',
    },
    {
        key: 'sort_by',
        type: 'select' as const,
        get options() {
            return Object.entries(props.sort_fields).map(([value, label]) => ({
                value,
                label: `Sort by ${label}`,
            }));
        },
    },
    {
        key: 'sort_direction',
        type: 'select' as const,
        options: [
            { value: 'asc', label: 'Ascending' },
            { value: 'desc', label: 'Descending' },
        ],
    },
];

function applyFilters(): void {
    router.get(index().url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedLogId.value = id;
    deleteDialogOpen.value = true;
}

function performDelete(): void {
    if (selectedLogId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(destroy(selectedLogId.value).url, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedLogId.value = null;
        },
    });
}

function requestBulkDelete(ids: Array<number | string>): void {
    if (!ids.length) {
        return;
    }

    pendingBulkIds.value = ids;
    bulkDeleteDialogOpen.value = true;
}

function performBulkDelete(): void {
    if (!pendingBulkIds.value.length) {
        return;
    }

    bulkDeleteProcessing.value = true;

    router.post(
        activityLogs.bulk.delete().url,
        { ids: pendingBulkIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
            onFinish: () => {
                bulkDeleteProcessing.value = false;
                bulkDeleteDialogOpen.value = false;
                pendingBulkIds.value = [];
            },
        },
    );
}

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('en-GB');
}
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

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="logs.data"
                :columns="columns"
                row-key="id"
                :selectable="permissions_meta.can_delete"
                empty-message="No activity logs found."
            >
                <template #cell-logged_in_user="{ row }">
                    {{ row.logged_in_user?.name ?? 'System' }}
                </template>

                <template #cell-related_to_user="{ row }">
                    {{ row.related_to_user?.name ?? 'N/A' }}
                </template>

                <template #cell-created_at="{ row }">
                    {{ formatDateTime(row.created_at) }}
                </template>

                <template
                    v-if="permissions_meta.can_delete"
                    #bulk-actions="{ selected }"
                >
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="requestBulkDelete(selected)"
                    >
                        Delete selected
                    </button>
                </template>

                <template #actions="{ row }">
                    <button
                        v-if="permissions_meta.can_delete"
                        type="button"
                        class="text-red-600 hover:text-red-900"
                        @click="requestDestroy(row.id)"
                    >
                        Delete
                    </button>
                </template>
            </ResourceTable>

            <Pagination
                :meta="logs.meta"
                :links="logs.links"
                resource-label="activity logs"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete activity log"
            description="This log entry will be permanently deleted. This cannot be undone."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="performDelete"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete selected activity logs"
            :description="`${pendingBulkIds.length} log entry(ies) will be permanently deleted. This cannot be undone.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="performBulkDelete"
        />
    </div>
</template>
