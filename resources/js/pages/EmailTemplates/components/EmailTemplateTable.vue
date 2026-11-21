<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as emailTemplatesEdit, show as emailTemplatesShow } from '@/routes/email-templates';
import type { EmailTemplate } from '@/types';

defineProps<{
    emailTemplates: EmailTemplate[];
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
    { key: 'subject', label: 'Subject' },
    { key: 'is_active', label: 'Active' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="emailTemplates"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No email templates found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-is_active="{ row }">
            {{ row.is_active ? 'Yes' : 'No' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="emailTemplatesShow.url(row.id)"
                :edit-href="emailTemplatesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>