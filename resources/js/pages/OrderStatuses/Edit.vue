<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as orderStatusesUpdate } from '@/routes/order-statuses';
import type { OrderStatus } from '@/types';
import OrderStatusForm from './components/OrderStatusForm.vue';
import type { OrderStatusFormData } from './components/OrderStatusForm.vue';

const props = defineProps<{
    orderStatus: OrderStatus;
}>();

const form = useForm<OrderStatusFormData>({
    title: props.orderStatus.title,
    description: props.orderStatus.description,
    background_colour: props.orderStatus.background_colour,
    text_colour: props.orderStatus.text_colour,
});

function onFormUpdate(updated: OrderStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).put(orderStatusesUpdate.url(props.orderStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Order Status
            </h1>
            <OrderStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Order Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
