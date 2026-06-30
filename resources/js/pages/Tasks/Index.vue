<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import {
    index as tasksIndex,
    create as tasksCreate,
    show as tasksShow,
    edit as tasksEdit,
    destroy as tasksDestroy,
    bulkDelete as tasksBulkDelete,
} from '@/routes/tasks';
import type {
    Task,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    tasks: {
        data: Task[];
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
    sort_by: urlParams.get('sort_by') ?? 'title',
    sort_direction: urlParams.get('sort_direction') ?? 'asc',
});

const selectedIds = ref<Array<number | string>>([]);

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status' },
    { key: 'assignee', label: 'Assigned To' },
    { key: 'due_date', label: 'Due Date' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search tasks…',
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
    router.get(tasksIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(tasksDestroy.url(id));
    }
}

function bulkDelete(ids: Array<number | string>): void {
    if (!ids.length) {
        return;
    }

    if (confirm(`Are you sure you want to delete ${ids.length} task(s)?`)) {
        router.post(
            tasksBulkDelete.url(),
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

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Tasks"
                :create-href="tasksCreate.url()"
                create-label="Add Task"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="tasks.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No tasks found."
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

                <template #cell-status="{ row }">
                    <span
                        v-if="row.status"
                        :style="{
                            backgroundColor:
                                row.status.background_colour ?? '#e2e8f0',
                            color: row.status.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ row.status.title }}
                    </span>
                    <span v-else>—</span>
                </template>

                <template #cell-assignee="{ row }">
                    {{ row.assignee?.name ?? '—' }}
                </template>

                <template #cell-due_date="{ row }">
                    {{ formatDate(row.due_date) }}
                </template>

                <template #actions="{ row }">
                    <Link :href="tasksShow.url(row.id)">View</Link>
                    <Link :href="tasksEdit.url(row.id)">Edit</Link>
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
                :meta="tasks.meta"
                :links="tasks.links"
                resource-label="tasks"
            />
        </div>
    </div>
</template>
