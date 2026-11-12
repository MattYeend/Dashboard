<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import {
    create as quotesCreate,
    destroy as quotesDestroy,
    exportMethod as quotesExport,
    index as quotesIndex,
} from '@/routes/quotes';
import quotesBulk from '@/routes/quotes/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Quote,
} from '@/types';
import QuoteTable from './components/QuoteTable.vue';

interface StatusOption {
    id: number;
    title: string;
}

interface Props {
    quotes: {
        data: Quote[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    statuses: StatusOption[];
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const {
    filters,
    filterFields: baseFilterFields,
    applyFilters,
} = useIndexFilters({
    url: () => quotesIndex.url(),
    searchPlaceholder: 'Search quotes…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const statusField = computed(() => ({
    key: 'status_id',
    type: 'select' as const,
    label: 'Status',
    options: props.statuses.map((status) => ({
        value: String(status.id),
        label: status.title,
    })),
}));

const filterFields = computed(() => [
    ...baseFilterFields.value,
    statusField.value,
]);

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(quotesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(quotesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Quotes" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Quotes"
                :create-href="quotesCreate.url()"
                create-label="Create quote"
                :can-create="permissions_meta.can_create"
                :export-href="quotesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <QuoteTable
                v-model:selected="selectedIds"
                :quotes="quotes.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="quotes.meta"
                :links="quotes.links"
                resource-label="quotes"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete quote"
            description="This quote will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete quotes"
            :description="`${bulkDeleteAction.ids.length} quote(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>