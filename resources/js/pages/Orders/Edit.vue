<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';
import { nullIfBlank } from '@/lib/forms';
import OrderForm from '@/pages/Orders/components/OrderForm.vue';
import { update as ordersUpdate } from '@/routes/orders';
import type { Order, OrderStatus } from '@/types';

interface Props {
    order: Order;
    statuses: OrderStatus[];
    orderableTypes: { value: string; label: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.order.title,
    description: props.order.description ?? '',
    notes: props.order.notes ?? '',
    subtotal: props.order.subtotal,
    discount_amount: props.order.discount_amount,
    tax_amount: props.order.tax_amount,
    total_amount: props.order.total_amount,
    ordered_at: props.order.ordered_at ?? '',
    due_at: props.order.due_at ?? '',
    completed_at: props.order.completed_at ?? '',
    status_id: props.order.status_id,
    orderable_type: props.order.orderable_type_key,
    orderable_id: props.order.orderable_id as number | null,
});

interface OrderableOption {
    value: number;
    label: string;
}

const orderableOptions = ref<OrderableOption[]>([]);

watch(
    () => form.orderable_type,
    async (type: string, previousType: string | undefined) => {
        const res = await axios.get('/orders/orderable-options', {
            params: { type },
        });

        orderableOptions.value = res.data;

        // Only reset the owner when the user changes type, not on initial load
        if (previousType !== undefined) {
            form.orderable_id = null;
        }
    },
    { immediate: true },
);

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        notes: nullIfBlank(data.notes),
        ordered_at: nullIfBlank(data.ordered_at),
        due_at: nullIfBlank(data.due_at),
        completed_at: nullIfBlank(data.completed_at),
    })).put(ordersUpdate.url(props.order.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Order</h1>

            <OrderForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:notes="form.notes"
                v-model:subtotal="form.subtotal"
                v-model:discount-amount="form.discount_amount"
                v-model:tax-amount="form.tax_amount"
                v-model:total-amount="form.total_amount"
                v-model:ordered-at="form.ordered_at"
                v-model:due-at="form.due_at"
                v-model:completed-at="form.completed_at"
                v-model:status-id="form.status_id"
                v-model:orderable-type="form.orderable_type"
                v-model:orderable-id="form.orderable_id"
                :statuses="props.statuses"
                :orderable-types="props.orderableTypes"
                :orderable-options="orderableOptions"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
