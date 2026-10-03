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
    create as notificationBroadcastsCreate,
    destroy as notificationBroadcastsDestroy,
    index as notificationBroadcastsIndex,
    send as notificationBroadcastsSend,
} from '@/routes/notification-broadcasts';
import notificationBroadcastsBulk from '@/routes/notification-broadcasts/bulk';
import type {
    NotificationBroadcast,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import NotificationBroadcastTable from './components/NotificationBroadcastTable.vue';

interface Props {
    notificationBroadcasts: {
        data: NotificationBroadcast[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => notificationBroadcastsIndex.url(),
    searchPlaceholder: 'Search notifications…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'created_at',
    defaultSortDirection: 'desc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(notificationBroadcastsDestroy.url(id), options),
);

const sendAction = useRowAction((id, options) =>
    router.post(notificationBroadcastsSend.url(id), {}, options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(notificationBroadcastsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Notifications" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Notifications"
                :create-href="notificationBroadcastsCreate.url()"
                create-label="New notification"
                :can-create="permissions_meta.can_create"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <NotificationBroadcastTable
                v-model:selected="selectedIds"
                :notification-broadcasts="notificationBroadcasts.data"
                @delete="deleteAction.request"
                @send="sendAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="notificationBroadcasts.meta"
                :links="notificationBroadcasts.links"
                resource-label="notifications"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete notification?"
            description="This cannot be undone."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete notifications"
            :description="`${bulkDeleteAction.ids.length} notification(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="sendAction.open"
            title="Send notification"
            description="This sends the notification to its audience now. This cannot be undone."
            confirm-label="Send"
            :processing="sendAction.processing"
            @confirm="sendAction.confirm"
        />
    </div>
</template>
