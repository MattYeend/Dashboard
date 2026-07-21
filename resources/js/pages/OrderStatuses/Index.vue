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
    index as orderStatusesIndex,
    create as orderStatusesCreate,
    show as orderStatusesShow,
    edit as orderStatusesEdit,
    destroy as orderStatusesDestroy,
} from '@/routes/order-statuses';
import orderStatusesBulk from '@/routes/order-statuses/bulk';
import type {
    OrderStatus,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    orderStatuses: {
        data: OrderStatus[];
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

const deleteDialogOpen = ref(false);
const selectedOrderStatusId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'description', label: 'Description' },
    { key: 'preview', label: 'Preview' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search order statuses…',
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
    router.get(orderStatusesIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedOrderStatusId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedOrderStatusId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(orderStatusesDestroy.url(selectedOrderStatusId.value), {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedOrderStatusId.value = null;
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
        orderStatusesBulk.delete.url(),
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
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Order Statuses"
                :create-href="orderStatusesCreate.url()"
                create-label="Add Order Status"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="orderStatuses.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No order statuses found."
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

                <template #actions="{ row }">
                    <Link :href="orderStatusesShow.url(row.id)">View</Link>
                    <Link :href="orderStatusesEdit.url(row.id)">Edit</Link>
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
                :meta="orderStatuses.meta"
                :links="orderStatuses.links"
                resource-label="order statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete order status"
            description="This order status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete order statuses"
            :description="`${pendingBulkIds.length} order status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />
    </div>
</template>
