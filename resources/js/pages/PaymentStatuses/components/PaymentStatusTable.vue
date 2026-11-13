<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import {
    edit as paymentStatusesEdit,
    show as paymentStatusesShow,
} from '@/routes/payment-statuses';
import type { PaymentStatus } from '@/types';

const props = defineProps<{
    paymentStatuses: PaymentStatus[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'description', label: 'Description' },
    { key: 'background_colour', label: 'Background' },
    { key: 'text_colour', label: 'Text' },
];

const systemIds = computed<Set<number>>(
    () =>
        new Set(
            props.paymentStatuses
                .filter((status) => status.is_system)
                .map((status) => status.id),
        ),
);

function deletableOnly(ids: Array<number | string>): Array<number | string> {
    return ids.filter((id) => !systemIds.value.has(Number(id)));
}
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="paymentStatuses"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No payment statuses found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton
                @click="emit('bulkDelete', deletableOnly(selection))"
            >
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-title="{ row }">
            <span
                class="inline-block rounded-md border-2 px-2 py-0.5 text-sm font-medium"
                :style="{
                    color: row.text_colour,
                    borderColor: row.background_colour,
                }"
            >
                {{ row.title }}
            </span>
            <span
                v-if="row.is_system"
                class="ml-2 text-xs text-muted-foreground"
            >
                System
            </span>
        </template>

        <template #cell-description="{ row }">
            {{ row.description ?? '' }}
        </template>

        <template #cell-background_colour="{ row }">
            <span class="font-mono text-sm">{{ row.background_colour }}</span>
        </template>

        <template #cell-text_colour="{ row }">
            <span class="font-mono text-sm">{{ row.text_colour }}</span>
        </template>

        <template #actions="{ row }">
            <div v-if="row.is_system" class="flex gap-3 text-sm">
                <Link :href="paymentStatusesShow.url(row.id)" class="underline">
                    View
                </Link>
                <Link :href="paymentStatusesEdit.url(row.id)" class="underline">
                    Edit
                </Link>
            </div>
            <RowActions
                v-else
                :show-href="paymentStatusesShow.url(row.id)"
                :edit-href="paymentStatusesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
