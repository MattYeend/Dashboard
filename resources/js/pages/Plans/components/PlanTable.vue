<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatMoney } from '@/lib/formatters';
import { edit as plansEdit, show as plansShow } from '@/routes/plans';
import type { Plan } from '@/types';

defineProps<{
    plans: Plan[];
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
    { key: 'price_per_user_per_month', label: 'Price' },
    { key: 'is_active', label: 'Active' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="plans"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No plans found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-name="{ row }">
            <span class="font-medium text-gray-300">{{ row.name }}</span>
        </template>

        <template #cell-price_per_user_per_month="{ row }">
            {{ formatMoney(row.price_per_user_per_month) }}
        </template>

        <template #cell-is_active="{ row }">
            {{ row.is_active ? 'Yes' : 'No' }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="plansShow.url(row.id)"
                :edit-href="plansEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
