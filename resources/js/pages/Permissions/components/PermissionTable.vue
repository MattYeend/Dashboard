<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as permissionsEdit, show as permissionsShow } from '@/routes/permissions';
import type { Permission } from '@/types';

defineProps<{
    permissions: Permission[];
    trashedOnly: boolean;
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    restore: [id: number];
    forceDelete: [id: number];
    bulkDelete: [ids: Array<number | string>];
    bulkRestore: [ids: Array<number | string>];
}>();

function formatRoles(roles?: Array<{ name: string }>): string {
    return roles?.map((role) => role.name).join(', ') || '-';
}

const columns: ResourceTableColumn[] = [
    { key: 'name', label: 'Name' },
    { key: 'guard_name', label: 'Guard' },
    { key: 'roles', label: 'Roles' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="permissions"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No permissions found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton
                v-if="!trashedOnly"
                @click="emit('bulkDelete', selection)"
            >
                Delete selected
            </BulkActionButton>
            <BulkActionButton
                v-else
                variant="neutral"
                @click="emit('bulkRestore', selection)"
            >
                Restore selected
            </BulkActionButton>
        </template>

        <template #cell-roles="{ row }">
            {{ formatRoles(row.roles) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="permissionsShow.url(row.id)"
                :edit-href="permissionsEdit.url(row.id)"
                :trashed="Boolean(row.deleted_at)"
                @delete="emit('delete', row.id)"
                @restore="emit('restore', row.id)"
                @force-delete="emit('forceDelete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
