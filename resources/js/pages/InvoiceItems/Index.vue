<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import { show as invoicesShow } from '@/routes/invoices';
import {
    index as invoiceItemsIndex,
    create as invoiceItemsCreate,
    show as invoiceItemsShow,
    edit as invoiceItemsEdit,
    destroy as invoiceItemsDestroy,
    restore as invoiceItemsRestore,
    forceDelete as invoiceItemsForceDelete,
} from '@/routes/invoices/items';
import invoiceItemsBulk from '@/routes/invoices/items/bulk';
import type {
    Invoice,
    InvoiceItem,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    invoice: Invoice;
    invoice_items: {
        data: InvoiceItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const urlParams = new URLSearchParams(window.location.search);

const filters = ref({
    search: urlParams.get('search') ?? '',
    trashed: urlParams.get('trashed') ?? '',
    sort_by: urlParams.get('sort_by') ?? 'position',
    sort_direction: urlParams.get('sort_direction') ?? 'asc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteDialogOpen = ref(false);
const selectedItemId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const restoreDialogOpen = ref(false);
const selectedRestoreId = ref<number | null>(null);
const restoreProcessing = ref(false);

const forceDeleteDialogOpen = ref(false);
const selectedForceDeleteId = ref<number | null>(null);
const forceDeleteProcessing = ref(false);

const bulkRestoreDialogOpen = ref(false);
const pendingBulkRestoreIds = ref<Array<number | string>>([]);
const bulkRestoreProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'description', label: 'Description' },
    { key: 'quantity', label: 'Quantity' },
    { key: 'unit_price', label: 'Unit Price' },
    { key: 'tax_rate', label: 'Tax Rate' },
    { key: 'total', label: 'Total' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search items…',
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
    router.get(
        invoiceItemsIndex.url({ invoice: props.invoice.id }),
        filters.value,
        {
            preserveState: true,
            replace: true,
        },
    );
}

function requestDestroy(id: number): void {
    selectedItemId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedItemId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(
        invoiceItemsDestroy.url({
            invoice: props.invoice.id,
            invoiceItem: selectedItemId.value,
        }),
        {
            preserveScroll: true,
            onFinish: () => {
                deleteProcessing.value = false;
                deleteDialogOpen.value = false;
                selectedItemId.value = null;
            },
        },
    );
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
        invoiceItemsBulk.delete.url({ invoice: props.invoice.id }),
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

function requestRestore(id: number): void {
    selectedRestoreId.value = id;
    restoreDialogOpen.value = true;
}

function restore(): void {
    if (selectedRestoreId.value === null) {
        return;
    }

    restoreProcessing.value = true;

    router.post(
        invoiceItemsRestore.url({
            invoice: props.invoice.id,
            id: selectedRestoreId.value,
        }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                restoreProcessing.value = false;
                restoreDialogOpen.value = false;
                selectedRestoreId.value = null;
            },
        },
    );
}

function requestForceDelete(id: number): void {
    selectedForceDeleteId.value = id;
    forceDeleteDialogOpen.value = true;
}

function forceDelete(): void {
    if (selectedForceDeleteId.value === null) {
        return;
    }

    forceDeleteProcessing.value = true;

    router.delete(
        invoiceItemsForceDelete.url({
            invoice: props.invoice.id,
            id: selectedForceDeleteId.value,
        }),
        {
            preserveScroll: true,
            onFinish: () => {
                forceDeleteProcessing.value = false;
                forceDeleteDialogOpen.value = false;
                selectedForceDeleteId.value = null;
            },
        },
    );
}

function requestBulkRestore(ids: Array<number | string>): void {
    if (!ids.length) {
        return;
    }

    pendingBulkRestoreIds.value = ids;
    bulkRestoreDialogOpen.value = true;
}

function bulkRestore(): void {
    if (!pendingBulkRestoreIds.value.length) {
        return;
    }

    bulkRestoreProcessing.value = true;

    router.post(
        invoiceItemsBulk.restore.url({ invoice: props.invoice.id }),
        { ids: pendingBulkRestoreIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
            onFinish: () => {
                bulkRestoreProcessing.value = false;
                bulkRestoreDialogOpen.value = false;
                pendingBulkRestoreIds.value = [];
            },
        },
    );
}

function formatMoney(pence: number): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(pence / 100);
}
</script>

<template>
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
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="invoice_items.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No invoice items found."
            >
                <template #bulk-actions="{ selected }">
                    <button
                        v-if="filters.trashed !== 'only'"
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="requestBulkDelete(selected)"
                    >
                        Delete selected
                    </button>
                    <button
                        v-else
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium"
                        @click="requestBulkRestore(selected)"
                    >
                        Restore selected
                    </button>
                </template>

                <template #cell-description="{ row }">
                    <span class="font-medium text-gray-300">
                        {{ row.description }}
                    </span>
                </template>

                <template #cell-unit_price="{ row }">
                    {{ formatMoney(row.unit_price) }}
                </template>

                <template #cell-tax_rate="{ row }">
                    {{ row.tax_rate }}%
                </template>

                <template #cell-total="{ row }">
                    {{ formatMoney(row.total) }}
                </template>

                <template #actions="{ row }">
                    <Link
                        :href="
                            invoiceItemsShow.url({
                                invoice: invoice.id,
                                invoiceItem: row.id,
                            })
                        "
                    >
                        View
                    </Link>
                    <template v-if="!row.deleted_at">
                        <Link
                            :href="
                                invoiceItemsEdit.url({
                                    invoice: invoice.id,
                                    invoiceItem: row.id,
                                })
                            "
                        >
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-900"
                            @click="requestDestroy(row.id)"
                        >
                            Delete
                        </button>
                    </template>
                    <template v-else>
                        <button type="button" @click="requestRestore(row.id)">
                            Restore
                        </button>
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-900"
                            @click="requestForceDelete(row.id)"
                        >
                            Delete permanently
                        </button>
                    </template>
                </template>
            </ResourceTable>

            <Pagination
                :meta="invoice_items.meta"
                :links="invoice_items.links"
                resource-label="items"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete invoice item"
            description="This invoice item will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete invoice items"
            :description="`${pendingBulkIds.length} item(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />

        <ConfirmDialog
            v-model:open="restoreDialogOpen"
            title="Restore invoice item"
            description="This invoice item will be restored from trash."
            confirm-label="Restore"
            :processing="restoreProcessing"
            @confirm="restore"
        />

        <ConfirmDialog
            v-model:open="forceDeleteDialogOpen"
            title="Permanently delete invoice item"
            description="This cannot be undone. The invoice item will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteProcessing"
            @confirm="forceDelete"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreDialogOpen"
            title="Restore invoice items"
            :description="`${pendingBulkRestoreIds.length} item(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreProcessing"
            @confirm="bulkRestore"
        />
    </div>
</template>
