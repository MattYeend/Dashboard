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
    create as dealsCreate,
    destroy as dealsDestroy,
    exportMethod as dealsExport,
    index as dealsIndex,
} from '@/routes/deals';
import dealsBulk from '@/routes/deals/bulk';
import type {
    Deal,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import DealTable from './components/DealTable.vue';

interface Props {
    deals: {
        data: Deal[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => dealsIndex.url(),
    searchPlaceholder: 'Search deals…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'title',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(dealsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(dealsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Deals" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Deals"
                :create-href="dealsCreate.url()"
                create-label="Add Deal"
                :can-create="permissions_meta.can_create"
                :export-href="dealsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <DealTable
                v-model:selected="selectedIds"
                :deals="deals.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="deals.meta"
                :links="deals.links"
                resource-label="deals"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete deal"
            description="This deal will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete deals"
            :description="`${bulkDeleteAction.ids.length} deal(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
