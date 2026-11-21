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
    create as emailTemplatesCreate,
    destroy as emailTemplatesDestroy,
    exportMethod as emailTemplatesExport,
    index as emailTemplatesIndex,
} from '@/routes/email-templates';
import emailTemplatesBulk from '@/routes/email-templates/bulk';
import type {
    EmailTemplate,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import EmailTemplateTable from './components/EmailTemplateTable.vue';

interface Props {
    emailTemplates: {
        data: EmailTemplate[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => emailTemplatesIndex.url(),
    searchPlaceholder: 'Search email templates…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(emailTemplatesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) =>
        router.post(emailTemplatesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Email templates" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Email templates"
                :create-href="emailTemplatesCreate.url()"
                create-label="Add Email Template"
                :can-create="permissions_meta.can_create"
                :export-href="emailTemplatesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <EmailTemplateTable
                v-model:selected="selectedIds"
                :email-templates="emailTemplates.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="emailTemplates.meta"
                :links="emailTemplates.links"
                resource-label="email templates"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete email template"
            description="This email template will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete email templates"
            :description="`${bulkDeleteAction.ids.length} email template(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
