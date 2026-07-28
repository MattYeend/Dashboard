<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import { Button } from '@/components/ui/button';
import { show as pipelinesShow } from '@/routes/pipelines';
import {
    index as pipelineStagesIndex,
    create as pipelineStagesCreate,
    show as pipelineStagesShow,
    edit as pipelineStagesEdit,
    destroy as pipelineStagesDestroy,
    restore as pipelineStagesRestore,
    forceDelete as pipelineStagesForceDelete,
} from '@/routes/pipelines/stages';
import pipelineStagesBulk from '@/routes/pipelines/stages/bulk';
import type {
    Pipeline,
    PipelineStage,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    pipeline: Pipeline;
    pipeline_stages: {
        data: PipelineStage[];
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
const selectedStageId = ref<number | null>(null);
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
    { key: 'position', label: 'Position' },
    { key: 'title', label: 'Title' },
    { key: 'is_won', label: 'Won' },
    { key: 'is_lost', label: 'Lost' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search stages…',
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
        pipelineStagesIndex.url({ pipeline: props.pipeline.id }),
        filters.value,
        {
            preserveState: true,
            replace: true,
        },
    );
}

function requestDestroy(id: number): void {
    selectedStageId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedStageId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(
        pipelineStagesDestroy.url({
            pipeline: props.pipeline.id,
            stage: selectedStageId.value,
        }),
        {
            preserveScroll: true,
            onFinish: () => {
                deleteProcessing.value = false;
                deleteDialogOpen.value = false;
                selectedStageId.value = null;
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
        pipelineStagesBulk.delete.url({ pipeline: props.pipeline.id }),
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
        pipelineStagesRestore.url({
            pipeline: props.pipeline.id,
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
        pipelineStagesForceDelete.url({
            pipeline: props.pipeline.id,
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
        pipelineStagesBulk.restore.url({ pipeline: props.pipeline.id }),
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
</script>

<template>
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
                :create-href="pipelineStagesCreate.url({ pipeline: pipeline.id })"
                create-label="Add Stage"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="pipeline_stages.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No pipeline stages found."
            >
                <template #bulk-actions="{ selected }">
                    <Button
                        v-if="filters.trashed !== 'only'"
                        variant="destructive"
                        size="sm"
                        @click="requestBulkDelete(selected)"
                    >
                        Delete selected
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        @click="requestBulkRestore(selected)"
                    >
                        Restore selected
                    </Button>
                </template>

                <template #cell-title="{ row }">
                    <span
                        class="inline-block rounded px-2 py-1 text-sm font-medium"
                        :style="{
                            backgroundColor: row.background_colour,
                            color: row.text_colour,
                        }"
                    >
                        {{ row.title }}
                    </span>
                </template>

                <template #cell-is_won="{ row }">
                    {{ row.is_won ? 'Yes' : 'No' }}
                </template>

                <template #cell-is_lost="{ row }">
                    {{ row.is_lost ? 'Yes' : 'No' }}
                </template>

                <template #actions="{ row }">
                    <Link
                        :href="
                            pipelineStagesShow.url({
                                pipeline: pipeline.id,
                                stage: row.id,
                            })
                        "
                    >
                        View
                    </Link>
                    <template v-if="!row.deleted_at">
                        <Link
                            :href="
                                pipelineStagesEdit.url({
                                    pipeline: pipeline.id,
                                    stage: row.id,
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
                :meta="pipeline_stages.meta"
                :links="pipeline_stages.links"
                resource-label="stages"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete pipeline stage"
            description="This pipeline stage will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete pipeline stages"
            :description="`${pendingBulkIds.length} stage(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />

        <ConfirmDialog
            v-model:open="restoreDialogOpen"
            title="Restore pipeline stage"
            description="This pipeline stage will be restored from trash."
            confirm-label="Restore"
            :processing="restoreProcessing"
            @confirm="restore"
        />

        <ConfirmDialog
            v-model:open="forceDeleteDialogOpen"
            title="Permanently delete pipeline stage"
            description="This cannot be undone. The pipeline stage will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteProcessing"
            @confirm="forceDelete"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreDialogOpen"
            title="Restore pipeline stages"
            :description="`${pendingBulkRestoreIds.length} stage(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreProcessing"
            @confirm="bulkRestore"
        />
    </div>
</template>