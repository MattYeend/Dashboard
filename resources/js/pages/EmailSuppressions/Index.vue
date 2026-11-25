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
    create as emailSuppressionsCreate,
    destroy as emailSuppressionsDestroy,
    exportMethod as emailSuppressionsExport,
    index as emailSuppressionsIndex,
} from '@/routes/email-suppressions';
import emailSuppressionsBulk from '@/routes/email-suppressions/bulk';
import type {
    EmailSuppression,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    SelectOption,
} from '@/types';
import EmailSuppressionTable from './components/EmailSuppressionTable.vue';

interface Props {
    emailSuppressions: {
        data: EmailSuppression[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    reasonOptions: SelectOption[];
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
    url: () => emailSuppressionsIndex.url(),
    searchPlaceholder: 'Search email suppressions…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const reasonField = computed(() => ({
    key: 'reason',
    type: 'select' as const,
    label: 'Reason',
    options: props.reasonOptions.map((option) => ({
        value: String(option.value),
        label: option.label,
    })),
}));

const filterFields = computed(() => [
    ...baseFilterFields.value,
    reasonField.value,
]);

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(emailSuppressionsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(emailSuppressionsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Email suppressions" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Email Suppressions"
                :create-href="emailSuppressionsCreate.url()"
                create-label="Add Email Suppression"
                :can-create="permissions_meta.can_create"
                :export-href="emailSuppressionsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <EmailSuppressionTable
                v-model:selected="selectedIds"
                :email-suppressions="emailSuppressions.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="emailSuppressions.meta"
                :links="emailSuppressions.links"
                resource-label="email suppressions"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete email suppression"
            description="Deleting this suppression re-subscribes the address, so future campaigns will email it again. Only continue if the recipient has asked to be emailed again."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete email suppressions"
            :description="`Deleting ${bulkDeleteAction.ids.length} suppression(s) re-subscribes those addresses, so future campaigns will email them again. Only continue if the recipients have asked to be emailed again.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
