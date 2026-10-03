<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { truncate } from '@/lib/formatters';
import { edit as companiesEdit, show as companiesShow } from '@/routes/companies';
import type { Company } from '@/types';

defineProps<{
    companies: Company[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'name', label: 'Name' },
    { key: 'industry', label: 'Industry' },
    { key: 'account_manager', label: 'Account Manager' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="companies"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No companies found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-name="{ row }">
            <span class="font-medium text-gray-300">{{ row.name }}</span>
        </template>

        <template #cell-industry="{ row }">
            {{ truncate(row.industry?.title, 20) }}
        </template>

        <template #cell-account_manager="{ row }">
            {{ row.account_manager?.name ?? '-' }}
        </template>

        <template #cell-email="{ row }">
            {{ truncate(row.email, 20) }}
        </template>

        <template #cell-phone="{ row }">
            {{ row.phone ?? '-' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="companiesShow.url(row.id)"
                :edit-href="companiesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
