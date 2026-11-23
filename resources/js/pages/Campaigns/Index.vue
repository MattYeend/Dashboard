<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import {
    create as campaignsCreate,
    destroy as campaignsDestroy,
    exportMethod as campaignsExport,
    index as campaignsIndex,
} from '@/routes/campaigns';
import campaignsBulk from '@/routes/campaigns/bulk';
import type {
    Campaign,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import CampaignTable from './components/CampaignTable.vue';

interface Props {
    campaigns: {
        data: Campaign[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    statuses: { id: number; title: string }[];
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const {
    filters,
    filterFields: baseFilterFields,
    applyFilters,
} = useIndexFilters({
    url: () => campaignsIndex.url(),
    searchPlaceholder: 'Search campaigns…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const statusField = computed(() => ({
    key: 'status_id',
    type: 'select' as const,
    label: 'Status',
    options: props.statuses.map((status) => ({
        value: String(status.id),
        label: status.title,
    })),
}));

const filterFields = computed(() => [
    ...baseFilterFields.value,
    statusField.value,
]);

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(campaignsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(campaignsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Campaigns" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Campaigns"
                :create-href="campaignsCreate.url()"
                create-label="Add Campaign"
                :can-create="permissions_meta.can_create"
                :export-href="campaignsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <CampaignTable
                v-model:selected="selectedIds"
                :campaigns="campaigns.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="campaigns.meta"
                :links="campaigns.links"
                resource-label="campaigns"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete campaign"
            description="This campaign will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete campaigns"
            :description="`${bulkDeleteAction.ids.length} campaign(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>