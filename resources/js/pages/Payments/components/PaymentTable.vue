<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as paymentsEdit, show as paymentsShow } from '@/routes/payments';
import type { Payment } from '@/types';

defineProps<{
    payments: Payment[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'paid_at', label: 'Date received' },
    { key: 'invoice', label: 'Invoice' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount' },
    { key: 'method_label', label: 'Method' },
    { key: 'status', label: 'Status' },
    { key: 'reference', label: 'Reference' },
];

/**
 * Only unlocked rows can be selected for bulk delete.
 */
function isSelectable(row: Payment): boolean {
    return !row.is_locked;
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB');
}

/**
 * Format a pounds string. Refunds are prefixed with a minus sign.
 */
function formatMoney(row: Payment): string {
    const formatted = new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: row.currency,
    }).format(Number(row.amount));

    return row.type === 'refund' ? `-${formatted}` : formatted;
}
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="payments"
        :columns="columns"
        row-key="id"
        selectable
        :row-selectable="isSelectable"
        empty-message="No payments found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-paid_at="{ row }">
            {{ formatDate(row.paid_at) }}
        </template>

        <template #cell-invoice="{ row }">
            {{ row.invoice?.invoice_number ?? '' }}
        </template>

        <template #cell-type="{ row }">
            {{ row.type === 'refund' ? 'Refund' : 'Payment' }}
        </template>

        <template #cell-amount="{ row }">
            {{ formatMoney(row) }}
        </template>

        <template #cell-status="{ row }">
            <span
                v-if="row.status"
                class="inline-flex rounded border px-2 py-0.5 text-xs font-medium"
                :style="{
                    color: row.status.text_colour,
                    borderColor: row.status.text_colour,
                }"
            >
                {{ row.status.title }}
            </span>
        </template>

        <template #cell-reference="{ row }">
            {{ row.reference ?? '' }}
        </template>

        <template #actions="{ row }">
            <Link
                v-if="row.is_locked"
                :href="paymentsShow.url(row.id)"
                class="text-sm font-medium"
            >
                View
            </Link>
            <RowActions
                v-else
                :show-href="paymentsShow.url(row.id)"
                :edit-href="
                    row.deleted_at ? undefined : paymentsEdit.url(row.id)
                "
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
