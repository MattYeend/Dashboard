<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import { show as invoicesShow } from '@/routes/invoices';
import {
    create as invoiceItemsCreate,
    destroy as invoiceItemsDestroy,
    exportMethod as invoiceItemsExport,
    forceDelete as invoiceItemsForceDelete,
    index as invoiceItemsIndex,
    restore as invoiceItemsRestore,
} from '@/routes/invoices/items';
import invoiceItemsBulk from '@/routes/invoices/items/bulk';
import type {
    Invoice,
    InvoiceItem,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import InvoiceItemTable from './components/InvoiceItemTable.vue';

interface Props {
    invoice: Invoice;
    invoice_items: {
        data: InvoiceItem[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => invoiceItemsIndex.url({ invoice: props.invoice.id }),
    searchPlaceholder: 'Search items…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'position',
});

const selectedIds = ref<Array<number | string>>([]);

function clearSelection(): void {
    selectedIds.value = [];
}

const deleteAction = useRowAction((id, options) =>
    router.delete(
        invoiceItemsDestroy.url({
            invoice: props.invoice.id,
            invoiceItem: id,
        }),
        options,
    ),
);

const restoreAction = useRowAction((id, options) =>
    router.post(
        invoiceItemsRestore.url({ invoice: props.invoice.id, id }),
        {},
        options,
    ),
);

const forceDeleteAction = useRowAction((id, options) =>
    router.delete(
        invoiceItemsForceDelete.url({ invoice: props.invoice.id, id }),
        options,
    ),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(
            invoiceItemsBulk.delete.url({ invoice: props.invoice.id }),
            { ids },
            options,
        ),
    clearSelection,
);

const bulkRestoreAction = useBulkAction(
    (ids, options) =>
        router.post(
            invoiceItemsBulk.restore.url({ invoice: props.invoice.id }),
            { ids },
            options,
        ),
    clearSelection,
);
</script>

<template>
    <Head :title="`Items - ${invoice.invoice_number}`" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-2">
                <Link
                    :href="invoicesShow.url(invoice.id)"
                    class="text-sm text-gray-400 hover:text-gray-300"
                >
                    &larr; Back to {{ invoice.invoice_number }}
                </Link>
            </div>

            <IndexHeader
                :title="`Items - ${invoice.invoice_number}`"
                :create-href="invoiceItemsCreate.url({ invoice: invoice.id })"
                create-label="Add Item"
                :can-create="permissions_meta.can_create"
                :export-href="invoiceItemsExport.url({ invoice: invoice.id })"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <InvoiceItemTable
                v-model:selected="selectedIds"
                :items="invoice_items.data"
                :invoice-id="invoice.id"
                :trashed-only="filters.trashed === 'only'"
                @delete="deleteAction.request"
                @restore="restoreAction.request"
                @force-delete="forceDeleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
                @bulk-restore="bulkRestoreAction.request"
            />

            <Pagination
                :meta="invoice_items.meta"
                :links="invoice_items.links"
                resource-label="items"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete invoice item"
            description="This invoice item will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete invoice items"
            :description="`${bulkDeleteAction.ids.length} item(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="restoreAction.open"
            title="Restore invoice item"
            description="This invoice item will be restored from trash."
            confirm-label="Restore"
            :processing="restoreAction.processing"
            @confirm="restoreAction.confirm"
        />

        <ConfirmDialog
            v-model:open="forceDeleteAction.open"
            title="Permanently delete invoice item"
            description="This cannot be undone. The invoice item will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteAction.processing"
            @confirm="forceDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreAction.open"
            title="Restore invoice items"
            :description="`${bulkRestoreAction.ids.length} item(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreAction.processing"
            @confirm="bulkRestoreAction.confirm"
        />
    </div>
</template>
