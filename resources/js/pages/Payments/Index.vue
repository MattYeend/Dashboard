<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import PaymentTable from '@/pages/Payments/components/PaymentTable.vue';
import {
    create as paymentsCreate,
    destroy as paymentsDestroy,
    exportMethod as paymentsExport,
    index as paymentsIndex,
} from '@/routes/payments';
import paymentsBulk from '@/routes/payments/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    Payment,
    PaymentMethodOption,
    PermissionsMeta,
} from '@/types';

interface StatusOption {
    id: number;
    title: string;
}

interface Props {
    payments: {
        data: Payment[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
    status_options: StatusOption[];
    methods: PaymentMethodOption[];
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => paymentsIndex.url(),
    searchPlaceholder: 'Search payments…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'paid_at',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(paymentsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(paymentsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Payments" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Payments"
                :create-href="paymentsCreate.url()"
                create-label="Record payment"
                :can-create="permissions_meta.can_create"
                :export-href="paymentsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <PaymentTable
                v-model:selected="selectedIds"
                :payments="payments.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="payments.meta"
                :links="payments.links"
                resource-label="payments"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete payment"
            description="This payment will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete payments"
            :description="`${bulkDeleteAction.ids.length} payment(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
