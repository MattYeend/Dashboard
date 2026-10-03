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
    create as plansCreate,
    destroy as plansDestroy,
    index as plansIndex,
} from '@/routes/plans';
import plansBulk from '@/routes/plans/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    Plan,
} from '@/types';
import PlanTable from './components/PlanTable.vue';

interface Props {
    plans: {
        data: Plan[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => plansIndex.url(),
    searchPlaceholder: 'Search plans…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    extraFilters: [
        {
            key: 'is_active',
            type: 'select',
            options: () => [
                { value: '', label: 'All' },
                { value: '1', label: 'Active' },
                { value: '0', label: 'Inactive' },
            ],
        },
    ],
    defaultSortBy: 'price_per_user_per_month',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(plansDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(plansBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Plans" />

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

            <PlanTable
                v-model:selected="selectedIds"
                :plans="plans.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="plans.meta"
                :links="plans.links"
                resource-label="plans"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete plan"
            description="This plan will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete plans"
            :description="`${bulkDeleteAction.ids.length} plan(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
