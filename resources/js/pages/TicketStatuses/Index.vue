<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import {
    index as ticketStatusesIndex,
    create as ticketStatusesCreate,
    show as ticketStatusesShow,
    edit as ticketStatusesEdit,
    destroy as ticketStatusesDestroy,
} from '@/routes/ticket-statuses';
import ticketStatusesBulk from '@/routes/ticket-statuses/bulk';
import type {
    TicketStatus,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    ticketStatuses: {
        data: TicketStatus[];
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
    sort_direction: 'asc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteDialogOpen = ref(false);
const selectedTicketStatusId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'description', label: 'Description' },
    { key: 'preview', label: 'Preview' },
    { key: 'created_at', label: 'Created' },
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
    router.get(ticketStatusesIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedTicketStatusId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedTicketStatusId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(ticketStatusesDestroy.url(selectedTicketStatusId.value), {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedTicketStatusId.value = null;
        },
    });
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
        ticketStatusesBulk.delete.url(),
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

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
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
                title="Ticket Statuses"
                :create-href="ticketStatusesCreate.url()"
                create-label="Add Ticket Status"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="ticketStatuses.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No ticket statuses found."
            >
                <template #bulk-actions="{ selected }">
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="requestBulkDelete(selected)"
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
                    {{ row.description ?? '-' }}
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

                <template #cell-created_at="{ row }">
                    {{ formatDate(row.created_at) }}
                </template>

                <template #actions="{ row }">
                    <Link :href="ticketStatusesShow.url(row.id)">View</Link>
                    <Link :href="ticketStatusesEdit.url(row.id)">Edit</Link>
                    <button
                        type="button"
                        class="text-red-600 hover:text-red-900"
                        @click="requestDestroy(row.id)"
                    >
                        Delete
                    </button>
                </template>
            </ResourceTable>

            <Pagination
                :meta="ticketStatuses.meta"
                :links="ticketStatuses.links"
                resource-label="ticket statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete ticket status"
            description="This ticket status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete ticket statuses"
            :description="`${pendingBulkIds.length} ticket status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />
    </div>
</template>
