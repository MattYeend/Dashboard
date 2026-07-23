<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import OrderBasicDetailsForm from '@/pages/Orders/components/OrderBasicDetailsForm.vue';
import OrderTypeForm from '@/pages/Orders/components/OrderTypeForm.vue';
import { index as ordersIndex } from '@/routes/orders';
import type { OrderStatus } from '@/types';

interface OrderFormData {
    title: string;
    description: string | null;
    notes: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    ordered_at: string | null;
    due_at: string | null;
    completed_at: string | null;
    status_id: number | null;
    orderable_type: string;
    orderable_id: number | null;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<OrderFormData>['errors']>;
    statuses: OrderStatus[];
    orderableTypes: { value: string; label: string }[];
    orderableOptions: { value: number; label: string }[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

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
const orderedAt = defineModel<string | null>('orderedAt', { default: null });
const dueAt = defineModel<string | null>('dueAt', { default: null });
const completedAt = defineModel<string | null>('completedAt', {
    default: null,
});
const statusId = defineModel<number | null>('statusId', { default: null });
const orderableType = defineModel<string>('orderableType', {
    required: true,
});
const orderableId = defineModel<number | null>('orderableId', {
    required: true,
});
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <OrderBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            v-model:notes="notes"
            v-model:subtotal="subtotal"
            v-model:discount-amount="discountAmount"
            v-model:tax-amount="taxAmount"
            v-model:total-amount="totalAmount"
            v-model:ordered-at="orderedAt"
            v-model:due-at="dueAt"
            v-model:completed-at="completedAt"
            v-model:status-id="statusId"
            :statuses="statuses"
            :errors="errors"
        />

        <OrderTypeForm
            v-model:orderable-type="orderableType"
            v-model:orderable-id="orderableId"
            :orderable-types="orderableTypes"
            :orderable-options="orderableOptions"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="ordersIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Order' : 'Create Order' }}
            </Button>
        </div>
    </form>
</template>
