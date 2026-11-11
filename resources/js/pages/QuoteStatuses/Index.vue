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
    create as quoteStatusesCreate,
    destroy as quoteStatusesDestroy,
    exportMethod as quoteStatusesExport,
    index as quoteStatusesIndex,
} from '@/routes/quote-statuses';
import quoteStatusesBulk from '@/routes/quote-statuses/bulk';
import type {
    QuoteStatus,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import QuoteStatusTable from './components/QuoteStatusTable.vue';

interface Props {
    quoteStatuses: {
        data: QuoteStatus[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => quoteStatusesIndex.url(),
    searchPlaceholder: 'Search quote statuses…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(quoteStatusesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(quoteStatusesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Quote statuses" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Quote Statuses"
                :create-href="quoteStatusesCreate.url()"
                create-label="Create Quote Status"
                :can-create="permissions_meta.can_create"
                :export-href="quoteStatusesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <QuoteStatusTable
                v-model:selected="selectedIds"
                :quote-statuses="quoteStatuses.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="quoteStatuses.meta"
                :links="quoteStatuses.links"
                resource-label="quote statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete quote status"
            description="This quote status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete quote statuses"
            :description="`${bulkDeleteAction.ids.length} quote status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
