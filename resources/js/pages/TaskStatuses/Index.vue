<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import {
    index as taskStatusesIndex,
    create as taskStatusesCreate,
    show as taskStatusesShow,
    edit as taskStatusesEdit,
    destroy as taskStatusesDestroy,
} from '@/routes/task-statuses';
import taskStatusesBulk from '@/routes/task-statuses/bulk';
import type {
    TaskStatus,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    taskStatuses: {
        data: TaskStatus[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const filters = ref({
    search: '',
    trashed: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'description', label: 'Description' },
    { key: 'preview', label: 'Preview' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search task statuses…',
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
    router.get(taskStatusesIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this task status?')) {
        router.delete(taskStatusesDestroy.url(id));
    }
}

function bulkDelete(ids: Array<number | string>): void {
    if (!ids.length) {
        return;
    }

    if (
        confirm(
            `Are you sure you want to delete ${ids.length} task status(es)?`,
        )
    ) {
        router.post(
            taskStatusesBulk.delete.url(),
            { ids },
            {
                preserveScroll: true,
                onSuccess: () => {
                    selectedIds.value = [];
                },
            },
        );
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Task Statuses"
                :create-href="taskStatusesCreate.url()"
                create-label="Add Task Status"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="taskStatuses.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No task statuses found."
            >
                <template #bulk-actions="{ selected }">
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="bulkDelete(selected)"
                    >
                        Delete selected
                    </button>
                </template>

                <template #cell-title="{ row }">
                    <span class="font-medium text-gray-300">
                        {{ row.title }}
                    </span>
                </template>

                <template #cell-description="{ row }">
                    {{ row.description ?? '—' }}
                </template>

                <template #cell-preview="{ row }">
                    <span
                        class="rounded px-2 py-0.5 text-xs font-medium"
                        :style="{
                            backgroundColor: row.background_colour,
                            color: row.text_colour,
                        }"
                    >
                        {{ row.title }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <Link :href="taskStatusesShow.url(row.id)">View</Link>
                    <Link :href="taskStatusesEdit.url(row.id)">Edit</Link>
                    <button
                        type="button"
                        class="text-red-600 hover:text-red-900"
                        @click="destroy(row.id)"
                    >
                        Delete
                    </button>
                </template>
            </ResourceTable>

            <Pagination
                :meta="taskStatuses.meta"
                :links="taskStatuses.links"
                resource-label="task statuses"
            />
        </div>
    </div>
</template>
