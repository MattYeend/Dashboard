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
    create as campaignSegmentsCreate,
    destroy as campaignSegmentsDestroy,
    exportMethod as campaignSegmentsExport,
    index as campaignSegmentsIndex,
} from '@/routes/campaign-segments';
import campaignSegmentsBulk from '@/routes/campaign-segments/bulk';
import type {
    CampaignSegment,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import CampaignSegmentTable from './components/CampaignSegmentTable.vue';

interface Props {
    campaignSegments: {
        data: CampaignSegment[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => campaignSegmentsIndex.url(),
    searchPlaceholder: 'Search campaign segments…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(campaignSegmentsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(campaignSegmentsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Campaign segments" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Campaign Segments"
                :create-href="campaignSegmentsCreate.url()"
                create-label="Add Campaign Segment"
                :can-create="permissions_meta.can_create"
                :export-href="campaignSegmentsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <CampaignSegmentTable
                v-model:selected="selectedIds"
                :campaign-segments="campaignSegments.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="campaignSegments.meta"
                :links="campaignSegments.links"
                resource-label="campaign segments"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete campaign segment"
            description="This campaign segment will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete campaign segments"
            :description="`${bulkDeleteAction.ids.length} campaign segment(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
