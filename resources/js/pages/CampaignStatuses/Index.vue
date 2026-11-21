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
    create as campaignStatusesCreate,
    destroy as campaignStatusesDestroy,
    exportMethod as campaignStatusesExport,
    index as campaignStatusesIndex,
} from '@/routes/campaign-statuses';
import campaignStatusesBulk from '@/routes/campaign-statuses/bulk';
import type {
    CampaignStatus,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import CampaignStatusTable from './components/CampaignStatusTable.vue';

interface Props {
    campaignStatuses: {
        data: CampaignStatus[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => campaignStatusesIndex.url(),
    searchPlaceholder: 'Search campaign statuses…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(campaignStatusesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(campaignStatusesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Campaign statuses" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Campaign Statuses"
                :create-href="campaignStatusesCreate.url()"
                create-label="Add Campaign Status"
                :can-create="permissions_meta.can_create"
                :export-href="campaignStatusesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <CampaignStatusTable
                v-model:selected="selectedIds"
                :campaign-statuses="campaignStatuses.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="campaignStatuses.meta"
                :links="campaignStatuses.links"
                resource-label="campaign statuses"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete campaign status"
            description="This campaign status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete campaign statuses"
            :description="`${bulkDeleteAction.ids.length} campaign status(es) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
