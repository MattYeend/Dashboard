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
    create as ticketPrioritiesCreate,
    destroy as ticketPrioritiesDestroy,
    exportMethod as ticketPrioritiesExport,
    index as ticketPrioritiesIndex,
} from '@/routes/ticket-priorities';
import ticketPrioritiesBulk from '@/routes/ticket-priorities/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    TicketPriority,
} from '@/types';
import TicketPriorityTable from './components/TicketPriorityTable.vue';

interface Props {
    ticketPriorities: {
        data: TicketPriority[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
    level_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => ticketPrioritiesIndex.url(),
    searchPlaceholder: 'Search ticket priorities…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    extraFilters: [
        {
            key: 'level',
            type: 'select',
            options: () =>
                Object.entries(props.level_filters).map(([value, label]) => ({
                    value,
                    label,
                })),
        },
    ],
    defaultSortBy: 'level',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(ticketPrioritiesDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(ticketPrioritiesBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Ticket priorities" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Ticket Priorities"
                :create-href="ticketPrioritiesCreate.url()"
                create-label="Add Ticket Priority"
                :can-create="permissions_meta.can_create"
                :export-href="ticketPrioritiesExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <TicketPriorityTable
                v-model:selected="selectedIds"
                :ticket-priorities="ticketPriorities.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="ticketPriorities.meta"
                :links="ticketPriorities.links"
                resource-label="ticket priorities"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete ticket priority"
            description="This ticket priority will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete ticket priorities"
            :description="`${bulkDeleteAction.ids.length} ticket priority(ies) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
