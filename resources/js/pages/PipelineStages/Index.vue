<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import { show as pipelinesShow } from '@/routes/pipelines';
import {
    create as pipelineStagesCreate,
    destroy as pipelineStagesDestroy,
    exportMethod as pipelineStagesExport,
    forceDelete as pipelineStagesForceDelete,
    index as pipelineStagesIndex,
    restore as pipelineStagesRestore,
} from '@/routes/pipelines/stages';
import pipelineStagesBulk from '@/routes/pipelines/stages/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Pipeline,
    PipelineStage,
} from '@/types';
import PipelineStageTable from './components/PipelineStageTable.vue';

interface Props {
    pipeline: Pipeline;
    pipeline_stages: {
        data: PipelineStage[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => pipelineStagesIndex.url({ pipeline: props.pipeline.id }),
    searchPlaceholder: 'Search stages…',
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
        pipelineStagesDestroy.url({ pipeline: props.pipeline.id, stage: id }),
        options,
    ),
);

const restoreAction = useRowAction((id, options) =>
    router.post(
        pipelineStagesRestore.url({ pipeline: props.pipeline.id, id }),
        {},
        options,
    ),
);

const forceDeleteAction = useRowAction((id, options) =>
    router.delete(
        pipelineStagesForceDelete.url({ pipeline: props.pipeline.id, id }),
        options,
    ),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(
            pipelineStagesBulk.delete.url({ pipeline: props.pipeline.id }),
            { ids },
            options,
        ),
    clearSelection,
);

const bulkRestoreAction = useBulkAction(
    (ids, options) =>
        router.post(
            pipelineStagesBulk.restore.url({ pipeline: props.pipeline.id }),
            { ids },
            options,
        ),
    clearSelection,
);
</script>

<template>
    <Head :title="`Stages - ${pipeline.title}`" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-2">
                <Link
                    :href="pipelinesShow.url(pipeline.id)"
                    class="text-sm text-gray-400 hover:text-gray-300"
                >
                    &larr; Back to {{ pipeline.title }}
                </Link>
            </div>

            <IndexHeader
                :title="`Stages - ${pipeline.title}`"
                :create-href="
                    pipelineStagesCreate.url({ pipeline: pipeline.id })
                "
                create-label="Add Stage"
                :can-create="permissions_meta.can_create"
                :export-href="
                    pipelineStagesExport.url({ pipeline: pipeline.id })
                "
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <PipelineStageTable
                v-model:selected="selectedIds"
                :pipeline-stages="pipeline_stages.data"
                :pipeline-id="pipeline.id"
                :trashed-only="filters.trashed === 'only'"
                @delete="deleteAction.request"
                @restore="restoreAction.request"
                @force-delete="forceDeleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
                @bulk-restore="bulkRestoreAction.request"
            />

            <Pagination
                :meta="pipeline_stages.meta"
                :links="pipeline_stages.links"
                resource-label="stages"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete pipeline stage"
            description="This pipeline stage will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete pipeline stages"
            :description="`${bulkDeleteAction.ids.length} stage(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="restoreAction.open"
            title="Restore pipeline stage"
            description="This pipeline stage will be restored from trash."
            confirm-label="Restore"
            :processing="restoreAction.processing"
            @confirm="restoreAction.confirm"
        />

        <ConfirmDialog
            v-model:open="forceDeleteAction.open"
            title="Permanently delete pipeline stage"
            description="This cannot be undone. The pipeline stage will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteAction.processing"
            @confirm="forceDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreAction.open"
            title="Restore pipeline stages"
            :description="`${bulkRestoreAction.ids.length} stage(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreAction.processing"
            @confirm="bulkRestoreAction.confirm"
        />
    </div>
</template>
