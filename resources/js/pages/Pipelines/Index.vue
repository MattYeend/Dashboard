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
    create as pipelinesCreate,
    destroy as pipelinesDestroy,
    exportMethod as pipelinesExport,
    index as pipelinesIndex,
} from '@/routes/pipelines';
import pipelinesBulk from '@/routes/pipelines/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Pipeline,
} from '@/types';
import PipelineTable from './components/PipelineTable.vue';

interface Props {
    pipelines: {
        data: Pipeline[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => pipelinesIndex.url(),
    searchPlaceholder: 'Search pipelines…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'title',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(pipelinesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(pipelinesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Pipelines" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Pipelines"
                :create-href="pipelinesCreate.url()"
                create-label="Add Pipeline"
                :can-create="permissions_meta.can_create"
                :export-href="pipelinesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <PipelineTable
                v-model:selected="selectedIds"
                :pipelines="pipelines.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="pipelines.meta"
                :links="pipelines.links"
                resource-label="pipelines"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete pipeline"
            description="This pipeline will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete pipelines"
            :description="`${bulkDeleteAction.ids.length} pipeline(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
