<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import permissionsBulk from '@/routes/permissions/bulk';
import permissionsMatrix from '@/routes/permissions/matrix';
import {
    index as permissionsIndex,
    show as permissionsShow,
    create as permissionsCreate,
    edit as permissionsEdit,
    destroy as permissionsDestroy,
    restore as permissionsRestore,
    forceDelete as permissionsForceDelete,
} from '@/routes/permissions';
import type {
    Pagination as PaginationMeta,
    Permission,
    PermissionsMeta,
} from '@/types';

interface Props {
    permissions: {
        data: Permission[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const urlParams = new URLSearchParams(window.location.search);

const filters = ref({
    search: urlParams.get('search') ?? '',
    trashed: urlParams.get('trashed') ?? '',
    sort_by: urlParams.get('sort_by') ?? 'name',
    sort_direction: urlParams.get('sort_direction') ?? 'asc',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteDialogOpen = ref(false);
const selectedPermissionId = ref<number | null>(null);
const deleteProcessing = ref(false);

const bulkDeleteDialogOpen = ref(false);
const pendingBulkIds = ref<Array<number | string>>([]);
const bulkDeleteProcessing = ref(false);

const restoreDialogOpen = ref(false);
const selectedRestoreId = ref<number | null>(null);
const restoreProcessing = ref(false);

const forceDeleteDialogOpen = ref(false);
const selectedForceDeleteId = ref<number | null>(null);
const forceDeleteProcessing = ref(false);

const bulkRestoreDialogOpen = ref(false);
const pendingBulkRestoreIds = ref<Array<number | string>>([]);
const bulkRestoreProcessing = ref(false);

const columns: ResourceTableColumn[] = [
    { key: 'name', label: 'Name' },
    { key: 'guard_name', label: 'Guard' },
    { key: 'roles', label: 'Roles' },
];

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search permissions…',
    },
    {
        key: 'trashed',
        type: 'select' as const,
        get options() {
            return Object.entries(props.trash_filters).map(
                ([value, label]) => ({
                    value,
                    label,
                }),
            );
        },
    },
    {
        key: 'sort_by',
        type: 'select' as const,
        get options() {
            return Object.entries(props.sort_fields).map(([value, label]) => ({
                value,
                label: `Sort by ${label}`,
            }));
        },
    },
    {
        key: 'sort_direction',
        type: 'select' as const,
        options: [
            { value: 'asc', label: 'Ascending' },
            { value: 'desc', label: 'Descending' },
        ],
    },
];

function applyFilters(): void {
    router.get(permissionsIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function requestDestroy(id: number): void {
    selectedPermissionId.value = id;
    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (selectedPermissionId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(permissionsDestroy.url(selectedPermissionId.value), {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedPermissionId.value = null;
        },
    });
}

function requestBulkDelete(ids: Array<number | string>): void {
    if (!ids.length) {
        return;
    }

    pendingBulkIds.value = ids;
    bulkDeleteDialogOpen.value = true;
}

function bulkDelete(): void {
    if (!pendingBulkIds.value.length) {
        return;
    }

    bulkDeleteProcessing.value = true;

    router.post(
        permissionsBulk.delete.url(),
        { ids: pendingBulkIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
            onFinish: () => {
                bulkDeleteProcessing.value = false;
                bulkDeleteDialogOpen.value = false;
                pendingBulkIds.value = [];
            },
        },
    );
}

function requestRestore(id: number): void {
    selectedRestoreId.value = id;
    restoreDialogOpen.value = true;
}

function restore(): void {
    if (selectedRestoreId.value === null) {
        return;
    }

    restoreProcessing.value = true;

    router.post(
        permissionsRestore.url({ id: selectedRestoreId.value }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                restoreProcessing.value = false;
                restoreDialogOpen.value = false;
                selectedRestoreId.value = null;
            },
        },
    );
}

function requestForceDelete(id: number): void {
    selectedForceDeleteId.value = id;
    forceDeleteDialogOpen.value = true;
}

function forceDelete(): void {
    if (selectedForceDeleteId.value === null) {
        return;
    }

    forceDeleteProcessing.value = true;

    router.delete(
        permissionsForceDelete.url({ id: selectedForceDeleteId.value }),
        {
            preserveScroll: true,
            onFinish: () => {
                forceDeleteProcessing.value = false;
                forceDeleteDialogOpen.value = false;
                selectedForceDeleteId.value = null;
            },
        },
    );
}

function requestBulkRestore(ids: Array<number | string>): void {
    if (!ids.length) {
        return;
    }

    pendingBulkRestoreIds.value = ids;
    bulkRestoreDialogOpen.value = true;
}

function bulkRestore(): void {
    if (!pendingBulkRestoreIds.value.length) {
        return;
    }

    bulkRestoreProcessing.value = true;

    router.post(
        permissionsBulk.restore.url(),
        { ids: pendingBulkRestoreIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
            onFinish: () => {
                bulkRestoreProcessing.value = false;
                bulkRestoreDialogOpen.value = false;
                pendingBulkRestoreIds.value = [];
            },
        },
    );
}
</script>

<template>
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

            <ResourceTable
                v-model:selected="selectedIds"
                :rows="permissions.data"
                :columns="columns"
                row-key="id"
                selectable
                empty-message="No permissions found."
            >
                <template #bulk-actions="{ selected }">
                    <button
                        v-if="filters.trashed !== 'only'"
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-500"
                        @click="requestBulkDelete(selected)"
                    >
                        Delete selected
                    </button>
                    <button
                        v-else
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium"
                        @click="requestBulkRestore(selected)"
                    >
                        Restore selected
                    </button>
                </template>

                <template #cell-roles="{ row }">
                    {{
                        row.roles
                            ?.map((r: { name: string }) => r.name)
                            .join(', ') || '—'
                    }}
                </template>

                <template #actions="{ row }">
                    <Link :href="permissionsShow.url(row.id)">View</Link>
                    <template v-if="!row.deleted_at">
                        <Link :href="permissionsEdit.url(row.id)">Edit</Link>
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-900"
                            @click="requestDestroy(row.id)"
                        >
                            Delete
                        </button>
                    </template>
                    <template v-else>
                        <button type="button" @click="requestRestore(row.id)">
                            Restore
                        </button>
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-900"
                            @click="requestForceDelete(row.id)"
                        >
                            Delete permanently
                        </button>
                    </template>
                </template>
            </ResourceTable>

            <Pagination
                :meta="permissions.meta"
                :links="permissions.links"
                resource-label="permissions"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete permission"
            description="This permission will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteDialogOpen"
            title="Delete permissions"
            :description="`${pendingBulkIds.length} permission(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteProcessing"
            @confirm="bulkDelete"
        />

        <ConfirmDialog
            v-model:open="restoreDialogOpen"
            title="Restore permission"
            description="This permission will be restored from trash."
            confirm-label="Restore"
            :processing="restoreProcessing"
            @confirm="restore"
        />

        <ConfirmDialog
            v-model:open="forceDeleteDialogOpen"
            title="Permanently delete permission"
            description="This cannot be undone. The permission will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteProcessing"
            @confirm="forceDelete"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreDialogOpen"
            title="Restore permissions"
            :description="`${pendingBulkRestoreIds.length} permission(s) will be restored from trash.`"
            confirm-label="Restore"
            :processing="bulkRestoreProcessing"
            @confirm="bulkRestore"
        />
    </div>
</template>
