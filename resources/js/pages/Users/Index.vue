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
    create as usersCreate,
    destroy as usersDestroy,
    exportMethod as usersExport,
    index as usersIndex,
} from '@/routes/users';
import usersBulk from '@/routes/users/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
    User,
} from '@/types';
import UserTable from './components/UserTable.vue';

interface Props {
    users: {
        data: User[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => usersIndex.url(),
    searchPlaceholder: 'Search users…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'name',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(usersDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(usersBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Users" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Users"
                :create-href="usersCreate.url()"
                create-label="Add User"
                :can-create="permissions_meta.can_create"
                :export-href="usersExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <UserTable
                v-model:selected="selectedIds"
                :users="users.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="users.meta"
                :links="users.links"
                resource-label="users"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete user"
            description="This user will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete users"
            :description="`${bulkDeleteAction.ids.length} user(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
