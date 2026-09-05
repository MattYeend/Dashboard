<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import type {
    Pagination as PaginationMeta,
    ReportPermissionsMeta,
    Report,
} from '@/types';
import {
    index as reportsIndex,
    show as reportsShow,
    create as reportsCreate,
    edit as reportsEdit,
    destroy as reportsDestroy,
    exportMethod as reportsExport,
} from '@/routes/reports';
import reportsBulk from '@/routes/reports/bulk';

interface Props {
    reports: {
        data: Report[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: PaginationMeta;
    };
    permissions_meta: ReportPermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const urlParams = new URLSearchParams(window.location.search);

const filters = ref({
    search: urlParams.get('search') ?? '',
    trashed: urlParams.get('trashed') ?? '',
    sort_by: urlParams.get('sort_by') ?? 'created_at',
    sort_direction: urlParams.get('sort_direction') ?? 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteDialogOpen = ref(false);
const selectedReportId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'type_label', label: 'Type' },
    { key: 'format', label: 'Format' },
    { key: 'is_scheduled', label: 'Scheduled' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search reports…',
    },
    {
        key: 'trashed',
        type: 'select' as const,
        get options() {
            return Object.entries(props.trash_filters).map(
                ([value, label]) => ({
                    value,
                    label,
                }),
            );
        },
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
    router.get(reportsIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedReportId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedReportId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(reportsDestroy.url(selectedReportId.value), {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedReportId.value = null;
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

function bulkDelete(): void {
    if (!pendingBulkIds.value.length) {
        return;
    }

    bulkDeleteProcessing.value = true;

    router.post(
        reportsBulk.delete.url(),
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
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Reports"
                :create-href="reportsCreate.url()"
                create-label="Add Report"
                :can-create="permissions_meta.can_create"
                :export-href="reportsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="reports.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No reports found."
            >
                <template #cell-is_scheduled="{ row }">
                    {{ row.is_scheduled ? 'Yes' : 'No' }}
                </template>

                <template #bulk-actions="{ selected }">
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="requestBulkDelete(selected)"
                    >
                        Delete selected
                    </button>
                </template>

                <template #actions="{ row }">
                    <Link :href="reportsShow.url(row.id)">View</Link>
                    <Link :href="reportsEdit.url(row.id)">Edit</Link>
                    <button
                        type="button"
                        class="text-red-600 hover:text-red-900"
                        @click="requestDestroy(row.id)"
                    >
                        Delete
                    </button>
                </template>
            </ResourceTable>

            <Pagination
                :meta="reports.meta"
                :links="reports.links"
                resource-label="reports"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete report"
            description="This report will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete reports"
            :description="`${pendingBulkIds.length} report(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />
    </div>
</template>
