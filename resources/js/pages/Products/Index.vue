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
    create as productsCreate,
    destroy as productsDestroy,
    exportMethod as productsExport,
    index as productsIndex,
} from '@/routes/products';
import productsBulk from '@/routes/products/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Product,
} from '@/types';
import ProductTable from './components/ProductTable.vue';

interface Props {
    products: {
        data: Product[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => productsIndex.url(),
    searchPlaceholder: 'Search products…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'name',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(productsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(productsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Products" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Products"
                :create-href="productsCreate.url()"
                create-label="Add Product"
                :can-create="permissions_meta.can_create"
                :export-href="productsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ProductTable
                v-model:selected="selectedIds"
                :products="products.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="products.meta"
                :links="products.links"
                resource-label="products"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete product"
            description="This product will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete products"
            :description="`${bulkDeleteAction.ids.length} product(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
