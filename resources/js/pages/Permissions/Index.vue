<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import {
    create as permissionsCreate,
    destroy as permissionsDestroy,
    forceDelete as permissionsForceDelete,
    index as permissionsIndex,
    restore as permissionsRestore,
} from '@/routes/permissions';
import permissionsBulk from '@/routes/permissions/bulk';
import permissionsMatrix from '@/routes/permissions/matrix';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    Permission,
    PermissionsMeta,
} from '@/types';
import PermissionTable from './components/PermissionTable.vue';

interface Props {
    permissions: {
        data: Permission[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => permissionsIndex.url(),
    searchPlaceholder: 'Search permissions…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'name',
});

const selectedIds = ref<Array<number | string>>([]);

function clearSelection(): void {
    selectedIds.value = [];
}

const deleteAction = useRowAction((id, options) =>
    router.delete(permissionsDestroy.url(id), options),
);

const restoreAction = useRowAction((id, options) =>
    router.post(permissionsRestore.url({ id }), {}, options),
);

const forceDeleteAction = useRowAction((id, options) =>
    router.delete(permissionsForceDelete.url({ id }), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(permissionsBulk.delete.url(), { ids }, options),
    clearSelection,
);

const bulkRestoreAction = useBulkAction(
    (ids, options) => router.post(permissionsBulk.restore.url(), { ids }, options),
    clearSelection,
);
</script>

<template>
    <Head title="Permissions" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Permissions"
                :create-href="permissionsCreate.url()"
                create-label="New permission"
                :can-create="permissions_meta.can_create"
            />

            <div class="mb-4 flex justify-end">
                <Link
                    :href="permissionsMatrix.index.url()"
                    class="text-sm text-gray-400 hover:text-gray-300"
                >
                    Assignment matrix
                </Link>
            </div>

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <PermissionTable
                v-model:selected="selectedIds"
                :permissions="permissions.data"
                :trashed-only="filters.trashed === 'only'"
                @delete="deleteAction.request"
                @restore="restoreAction.request"
                @force-delete="forceDeleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
                @bulk-restore="bulkRestoreAction.request"
            />

            <Pagination
                :meta="permissions.meta"
                :links="permissions.links"
                resource-label="permissions"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete permission"
            description="This permission will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete permissions"
            :description="`${bulkDeleteAction.ids.length} permission(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="restoreAction.open"
            title="Restore permission"
            description="This permission will be restored from trash."
            confirm-label="Restore"
            :processing="restoreAction.processing"
            @confirm="restoreAction.confirm"
        />

        <ConfirmDialog
            v-model:open="forceDeleteAction.open"
            title="Permanently delete permission"
            description="This cannot be undone. The permission will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteAction.processing"
            @confirm="forceDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreAction.open"
            title="Restore permissions"
            :description="`${bulkRestoreAction.ids.length} permission(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreAction.processing"
            @confirm="bulkRestoreAction.confirm"
        />
    </div>
</template>
