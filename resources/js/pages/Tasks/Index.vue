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
    create as tasksCreate,
    destroy as tasksDestroy,
    exportMethod as tasksExport,
    index as tasksIndex,
} from '@/routes/tasks';
import tasksBulk from '@/routes/tasks/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Task,
} from '@/types';
import TaskTable from './components/TaskTable.vue';

interface Props {
    tasks: {
        data: Task[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => tasksIndex.url(),
    searchPlaceholder: 'Search tasks…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'due_date',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(tasksDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(tasksBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Tasks" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Tasks"
                :create-href="tasksCreate.url()"
                create-label="Add Task"
                :can-create="permissions_meta.can_create"
                :export-href="tasksExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <TaskTable
                v-model:selected="selectedIds"
                :tasks="tasks.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="tasks.meta"
                :links="tasks.links"
                resource-label="tasks"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete task"
            description="This task will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete tasks"
            :description="`${bulkDeleteAction.ids.length} task(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
