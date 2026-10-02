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
    create as taskStatusesCreate,
    destroy as taskStatusesDestroy,
    exportMethod as taskStatusesExport,
    index as taskStatusesIndex,
} from '@/routes/task-statuses';
import taskStatusesBulk from '@/routes/task-statuses/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    TaskStatus,
} from '@/types';
import TaskStatusTable from './components/TaskStatusTable.vue';

interface Props {
    taskStatuses: {
        data: TaskStatus[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => taskStatusesIndex.url(),
    searchPlaceholder: 'Search task statuses…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(taskStatusesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(taskStatusesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Task statuses" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Task Statuses"
                :create-href="taskStatusesCreate.url()"
                create-label="Add Task Status"
                :can-create="permissions_meta.can_create"
                :export-href="taskStatusesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <TaskStatusTable
                v-model:selected="selectedIds"
                :task-statuses="taskStatuses.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="taskStatuses.meta"
                :links="taskStatuses.links"
                resource-label="task statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete task status"
            description="This task status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete task statuses"
            :description="`${bulkDeleteAction.ids.length} task status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
