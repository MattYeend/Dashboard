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
    create as dealStatusesCreate,
    destroy as dealStatusesDestroy,
    exportMethod as dealStatusesExport,
    index as dealStatusesIndex,
} from '@/routes/deal-statuses';
import dealStatusesBulk from '@/routes/deal-statuses/bulk';
import type {
    DealStatus,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import DealStatusTable from './components/DealStatusTable.vue';

interface Props {
    dealStatuses: {
        data: DealStatus[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => dealStatusesIndex.url(),
    searchPlaceholder: 'Search deal statuses…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(dealStatusesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(dealStatusesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Deal statuses" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Deal Statuses"
                :create-href="dealStatusesCreate.url()"
                create-label="Add Deal Status"
                :can-create="permissions_meta.can_create"
                :export-href="dealStatusesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <DealStatusTable
                v-model:selected="selectedIds"
                :deal-statuses="dealStatuses.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="dealStatuses.meta"
                :links="dealStatuses.links"
                resource-label="deal statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete deal status"
            description="This deal status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete deal statuses"
            :description="`${bulkDeleteAction.ids.length} deal status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
