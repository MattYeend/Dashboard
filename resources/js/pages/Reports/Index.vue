<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import {
    create as reportsCreate,
    destroy as reportsDestroy,
    exportMethod as reportsExport,
    index as reportsIndex,
} from '@/routes/reports';
import reportsBulk from '@/routes/reports/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    Report,
    ReportPermissionsMeta,
} from '@/types';
import ReportTable from './components/ReportTable.vue';

interface Props {
    reports: {
        data: Report[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: ReportPermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => reportsIndex.url(),
    searchPlaceholder: 'Search reports…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(reportsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(reportsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Reports" />

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

            <ReportTable
                v-model:selected="selectedIds"
                :reports="reports.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="reports.meta"
                :links="reports.links"
                resource-label="reports"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete report"
            description="This report will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete reports"
            :description="`${bulkDeleteAction.ids.length} report(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
