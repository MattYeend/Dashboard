<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import { show as quotesShow } from '@/routes/quotes';
import {
    create as quoteItemsCreate,
    destroy as quoteItemsDestroy,
    exportMethod as quoteItemsExport,
    forceDelete as quoteItemsForceDelete,
    index as quoteItemsIndex,
    restore as quoteItemsRestore,
} from '@/routes/quotes/items';
import quoteItemsBulk from '@/routes/quotes/items/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Quote,
    QuoteItem,
} from '@/types';
import QuoteItemTable from './components/QuoteItemTable.vue';

interface Props {
    quote: Quote;
    quote_items: {
        data: QuoteItem[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => quoteItemsIndex.url({ quote: props.quote.id }),
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
        quoteItemsDestroy.url({ quote: props.quote.id, quoteItem: id }),
        options,
    ),
);

const restoreAction = useRowAction((id, options) =>
    router.post(
        quoteItemsRestore.url({ quote: props.quote.id, id }),
        {},
        options,
    ),
);

const forceDeleteAction = useRowAction((id, options) =>
    router.delete(
        quoteItemsForceDelete.url({ quote: props.quote.id, id }),
        options,
    ),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(
            quoteItemsBulk.delete.url({ quote: props.quote.id }),
            { ids },
            options,
        ),
    clearSelection,
);

const bulkRestoreAction = useBulkAction(
    (ids, options) =>
        router.post(
            quoteItemsBulk.restore.url({ quote: props.quote.id }),
            { ids },
            options,
        ),
    clearSelection,
);
</script>

<template>
    <Head :title="`Items - ${quote.quote_number}`" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-2">
                <Link
                    :href="quotesShow.url(quote.id)"
                    class="text-sm text-gray-400 hover:text-gray-300"
                >
                    &larr; Back to {{ quote.quote_number }}
                </Link>
            </div>

            <IndexHeader
                :title="`Items - ${quote.quote_number}`"
                :create-href="quoteItemsCreate.url({ quote: quote.id })"
                create-label="Add Item"
                :can-create="permissions_meta.can_create"
                :export-href="quoteItemsExport.url({ quote: quote.id })"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <QuoteItemTable
                v-model:selected="selectedIds"
                :items="quote_items.data"
                :quote-id="quote.id"
                :currency="quote.currency"
                :trashed-only="filters.trashed === 'only'"
                @delete="deleteAction.request"
                @restore="restoreAction.request"
                @force-delete="forceDeleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
                @bulk-restore="bulkRestoreAction.request"
            />

            <Pagination
                :meta="quote_items.meta"
                :links="quote_items.links"
                resource-label="items"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete quote item"
            description="This quote item will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete quote items"
            :description="`${bulkDeleteAction.ids.length} item(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="restoreAction.open"
            title="Restore quote item"
            description="This quote item will be restored from trash."
            confirm-label="Restore"
            :processing="restoreAction.processing"
            @confirm="restoreAction.confirm"
        />

        <ConfirmDialog
            v-model:open="forceDeleteAction.open"
            title="Permanently delete quote item"
            description="This cannot be undone. The quote item will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteAction.processing"
            @confirm="forceDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreAction.open"
            title="Restore quote items"
            :description="`${bulkRestoreAction.ids.length} item(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreAction.processing"
            @confirm="bulkRestoreAction.confirm"
        />
    </div>
</template>
