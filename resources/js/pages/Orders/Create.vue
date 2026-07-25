<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';
import { nullIfBlank } from '@/lib/forms';
import OrderForm from '@/pages/Orders/components/OrderForm.vue';
import { store as ordersStore } from '@/routes/orders';
import type { OrderStatus } from '@/types';

interface Props {
    statuses: OrderStatus[];
    orderableTypes: { value: string; label: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    notes: '',
    subtotal: 0,
    discount_amount: 0,
    tax_amount: 0,
    total_amount: 0,
    ordered_at: '',
    due_at: '',
    completed_at: '',
    status_id: null as number | null,
    orderable_type: '',
    orderable_id: null as number | null,
});

interface OrderableOption {
    value: number;
    label: string;
}

const orderableOptions = ref<OrderableOption[]>([]);

watch(
    () => form.orderable_type,
    async (type: string) => {
        if (!type) {
            orderableOptions.value = [];
            form.orderable_id = null;

            return;
        }

        const res = await axios.get('/orders/orderable-options', {
            params: { type },
        });

        orderableOptions.value = res.data;
        form.orderable_id = null;
    },
);

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        notes: nullIfBlank(data.notes),
        ordered_at: nullIfBlank(data.ordered_at),
        due_at: nullIfBlank(data.due_at),
        completed_at: nullIfBlank(data.completed_at),
    })).post(ordersStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Order</h1>

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
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
