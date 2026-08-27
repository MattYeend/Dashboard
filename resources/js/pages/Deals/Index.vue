<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import {
    index as dealsIndex,
    show as dealsShow,
    create as dealsCreate,
    edit as dealsEdit,
    destroy as dealsDestroy,
    exportMethod as dealsExport,
} from '@/routes/deals';
import dealsBulk from '@/routes/deals/bulk';
import type {
    Deal,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    deals: {
        data: Deal[];
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

const deleteDialogOpen = ref(false);
const selectedDealId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'stage', label: 'Stage' },
    { key: 'status', label: 'Status' },
    { key: 'value', label: 'Value' },
    { key: 'company', label: 'Company' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search deals…',
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
    router.get(dealsIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedDealId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedDealId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(dealsDestroy.url(selectedDealId.value), {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedDealId.value = null;
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
        dealsBulk.delete.url(),
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
                title="Deals"
                :create-href="dealsCreate.url()"
                create-label="Add Deal"
                :can-create="permissions_meta.can_create"
                :export-href="dealsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="deals.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No deals found."
            >
                <template #cell-stage="{ row }">
                    <span
                        v-if="row.stage"
                        :style="{
                            backgroundColor:
                                row.stage.background_colour ?? '#e2e8f0',
                            color: row.stage.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ row.stage.title }}
                    </span>
                    <span v-else>-</span>
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
                    <span v-else>-</span>
                </template>
                <template #cell-value="{ row }">
                    {{ row.currency }} {{ row.value }}
                </template>
                <template #cell-company="{ row }">
                    {{ row.company?.name ?? '-' }}
                </template>

                <template #bulk-actions="{ selected }">
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="requestBulkDelete(selected)"
                    >
                        Delete selected
                    </button>
                </template>

                <template #actions="{ row }">
                    <Link :href="dealsShow.url(row.id)">View</Link>
                    <Link :href="dealsEdit.url(row.id)">Edit</Link>
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
                :meta="deals.meta"
                :links="deals.links"
                resource-label="deals"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete deal"
            description="This deal will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete deals"
            :description="`${pendingBulkIds.length} deal(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />
    </div>
</template>
