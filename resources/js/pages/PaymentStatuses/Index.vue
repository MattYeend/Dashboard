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
    create as paymentStatusesCreate,
    destroy as paymentStatusesDestroy,
    exportMethod as paymentStatusesExport,
    index as paymentStatusesIndex,
} from '@/routes/payment-statuses';
import paymentStatusesBulk from '@/routes/payment-statuses/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PaymentStatus,
    PermissionsMeta,
} from '@/types';

import PaymentStatusTable from './components/PaymentStatusTable.vue';

interface Props {
    paymentStatuses: {
        data: PaymentStatus[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => paymentStatusesIndex.url(),
    searchPlaceholder: 'Search payment statuses…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'title',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(paymentStatusesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(paymentStatusesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Payment statuses" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Payment statuses"
                :create-href="paymentStatusesCreate.url()"
                create-label="Add payment status"
                :can-create="permissions_meta.can_create"
                :export-href="paymentStatusesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <PaymentStatusTable
                v-model:selected="selectedIds"
                :payment-statuses="paymentStatuses.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="paymentStatuses.meta"
                :links="paymentStatuses.links"
                resource-label="payment statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete payment status"
            description="This payment status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete payment statuses"
            :description="`${bulkDeleteAction.ids.length} payment status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
