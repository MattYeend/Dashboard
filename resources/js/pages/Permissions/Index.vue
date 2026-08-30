<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import PaginationComponent from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    index as permissionsIndex,
    create as permissionsCreate,
} from '@/routes/permissions';
import { matrix as permissionsMatrix } from '@/routes/permissions';
import type { Permission, PermissionsMeta, Pagination } from '@/types';

interface Props {
    permissions: { data: Permission[] } & Pagination;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const selected = ref<number[]>([]);
const confirmingDeleteId = ref<number | null>(null);

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'guard_name', label: 'Guard' },
    { key: 'roles', label: 'Roles' },
];

function confirmDelete(id: number) {
    confirmingDeleteId.value = id;
}

function performDelete() {
    if (confirmingDeleteId.value === null) {
        return;
    }

    router.delete(`/permissions/${confirmingDeleteId.value}`, {
        onFinish: () => {
            confirmingDeleteId.value = null;
        },
    });
}
</script>

<template>
    <div>
        <IndexHeader
            title="Permissions"
            :create-href="permissionsCreate().url"
            create-label="New permission"
            :can-create="props.permissions_meta.can_create"
        />

        <div class="mb-4 flex justify-end">
            <Button as-child variant="outline">
                <a :href="permissionsMatrix().url">Assignment matrix</a>
            </Button>
        </div>

        <FilterBar :action="permissionsIndex().url" />

        <ResourceTable
            :columns="columns"
            :data="props.permissions.data"
            v-model:selected="selected"
            selectable
        >
            <template #cell-roles="{ row }">
                {{
                    row.roles
                        ?.map((r: { name: string }) => r.name)
                        .join(', ') || '—'
                }}
            </template>
            <template #actions="{ row }">
                <a :href="`/permissions/${row.id}`">View</a>
                <a :href="`/permissions/${row.id}/edit`">Edit</a>
                <button @click="confirmDelete(row.id)">Delete</button>
            </template>
        </ResourceTable>

        <PaginationComponent
            :meta="props.permissions"
            resource-label="permissions"
        />

        <ConfirmDialog
            :open="confirmingDeleteId !== null"
            title="Delete permission"
            description="Are you sure you want to delete this permission?"
            @confirm="performDelete"
        />
    </div>
</template>
