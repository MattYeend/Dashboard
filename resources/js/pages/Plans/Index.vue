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
    index as plansIndex,
    create as plansCreate,
    show as plansShow,
    edit as plansEdit,
    destroy as plansDestroy,
} from '@/routes/plans';
import plansBulk from '@/routes/plans/bulk';
import type {
    Plan,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    plans: {
        data: Plan[];
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
    is_active: '',
    trashed: '',
    sort_by: 'price_per_user_per_month',
    sort_direction: 'asc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteDialogOpen = ref(false);
const selectedPlanId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'name', label: 'Name' },
    { key: 'price_per_user_per_month', label: 'Price' },
    { key: 'is_active', label: 'Active' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search plans…',
    },
    {
        key: 'is_active',
        type: 'select' as const,
        options: [
            { value: '', label: 'All' },
            { value: '1', label: 'Active' },
            { value: '0', label: 'Inactive' },
        ],
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
    router.get(plansIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedPlanId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedPlanId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(plansDestroy.url(selectedPlanId.value), {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedPlanId.value = null;
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
        plansBulk.delete.url(),
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

function formatPrice(pence: number): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(pence / 100);
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Plans"
                :create-href="plansCreate.url()"
                create-label="Add Plan"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="plans.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No plans found."
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

                <template #cell-name="{ row }">
                    <span class="font-medium text-gray-300">
                        {{ row.name }}
                    </span>
                </template>

                <template #cell-price_per_user_per_month="{ row }">
                    {{ formatPrice(row.price_per_user_per_month) }}
                </template>

                <template #cell-is_active="{ row }">
                    {{ row.is_active ? 'Yes' : 'No' }}
                </template>

                <template #actions="{ row }">
                    <Link :href="plansShow.url(row.id)">View</Link>
                    <Link :href="plansEdit.url(row.id)">Edit</Link>
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
                :meta="plans.meta"
                :links="plans.links"
                resource-label="plans"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete plan"
            description="This plan will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete plans"
            :description="`${pendingBulkIds.length} plan(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />
    </div>
</template>
