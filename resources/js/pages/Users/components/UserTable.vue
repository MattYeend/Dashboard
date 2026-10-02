<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate } from '@/lib/formatters';
import {
    edit as usersEdit,
    impersonate as usersImpersonate,
    show as usersShow,
} from '@/routes/users';
import type { User } from '@/types';

defineProps<{
    users: User[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const columns: ResourceTableColumn[] = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Role' },
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="users"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No users found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-name="{ row }">
            <span class="font-medium text-gray-300">{{ row.name }}</span>
        </template>

        <template #cell-role="{ row }">
            <span class="capitalize">{{ row.role.replace('_', ' ') }}</span>
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="usersShow.url(row.id)"
                :edit-href="usersEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            >
                <Link
                    v-if="authUser.can_impersonate && row.id !== authUser.id"
                    :href="usersImpersonate.url(row.id)"
                    method="post"
                    as="button"
                >
                    Impersonate
                </Link>
            </RowActions>
        </template>
    </ResourceTable>
</template>
