<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import type { OrderStatus } from '@/types';
import OrderAmountFields from './OrderAmountFields.vue';
import OrderDateFields from './OrderDateFields.vue';
import OrderStatusField from './OrderStatusField.vue';
import OrderTitleFields from './OrderTitleFields.vue';

interface OrderBasicFormData {
    title: string;
    description: string | null;
    notes: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    currency: string;
    ordered_at: string | null;
    due_at: string | null;
    completed_at: string | null;
    status_id: number | null;
}

interface Props {
    statuses: OrderStatus[];
    errors: Partial<InertiaFormProps<OrderBasicFormData>['errors']>;
}

defineProps<Props>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const notes = defineModel<string | null>('notes', { default: null });
const subtotal = defineModel<number>('subtotal', { required: true });
const discountAmount = defineModel<number>('discountAmount', {
    required: true,
});
const taxAmount = defineModel<number>('taxAmount', { required: true });
const totalAmount = defineModel<number>('totalAmount', { required: true });
const currency = defineModel<string>('currency', { required: true });
const orderedAt = defineModel<string | null>('orderedAt', { default: null });
const dueAt = defineModel<string | null>('dueAt', { default: null });
const completedAt = defineModel<string | null>('completedAt', {
    default: null,
});
const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <div class="space-y-4">
        <OrderTitleFields
            v-model:title="title"
            v-model:description="description"
            v-model:notes="notes"
            :errors="errors"
        />

        <OrderAmountFields
            v-model:subtotal="subtotal"
            v-model:discount-amount="discountAmount"
            v-model:tax-amount="taxAmount"
            v-model:total-amount="totalAmount"
            v-model:currency="currency"
            :errors="errors"
        />

        <OrderDateFields
            v-model:ordered-at="orderedAt"
            v-model:due-at="dueAt"
            v-model:completed-at="completedAt"
            :errors="errors"
        />

        <OrderStatusField
            v-model:status-id="statusId"
            :statuses="statuses"
            :errors="errors"
        />
    </div>
</template>
