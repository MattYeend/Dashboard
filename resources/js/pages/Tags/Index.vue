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
    create as tagsCreate,
    destroy as tagsDestroy,
    exportMethod as tagsExport,
    index as tagsIndex,
} from '@/routes/tags';
import tagsBulk from '@/routes/tags/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Tag,
} from '@/types';
import TagTable from './components/TagTable.vue';

interface Props {
    tags: {
        data: Tag[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => tagsIndex.url(),
    searchPlaceholder: 'Search tags…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'name',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(tagsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(tagsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Tags" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Tags"
                :create-href="tagsCreate.url()"
                create-label="Add Tag"
                :can-create="permissions_meta.can_create"
                :export-href="tagsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <TagTable
                v-model:selected="selectedIds"
                :tags="tags.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="tags.meta"
                :links="tags.links"
                resource-label="tags"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete tag"
            description="This tag will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete tags"
            :description="`${bulkDeleteAction.ids.length} tag(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
