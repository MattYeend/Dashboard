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
    destroy as campaignRecipientsDestroy,
    exportMethod as campaignRecipientsExport,
    index as campaignRecipientsIndex,
} from '@/routes/campaign-recipients';
import campaignRecipientsBulk from '@/routes/campaign-recipients/bulk';
import type {
    CampaignRecipient,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import CampaignRecipientTable from './components/CampaignRecipientTable.vue';

interface Props {
    campaignRecipients: {
        data: CampaignRecipient[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => campaignRecipientsIndex.url(),
    searchPlaceholder: 'Search campaign recipients…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(campaignRecipientsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(campaignRecipientsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Campaign recipients" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Campaign Recipients"
                :can-create="false"
                :export-href="campaignRecipientsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <CampaignRecipientTable
                v-model:selected="selectedIds"
                :campaign-recipients="campaignRecipients.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="campaignRecipients.meta"
                :links="campaignRecipients.links"
                resource-label="campaign recipients"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete campaign recipient"
            description="This campaign recipient will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete campaign recipients"
            :description="`${bulkDeleteAction.ids.length} campaign recipient(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>