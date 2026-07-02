<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as orderStatusesStore } from '@/routes/order-statuses';
import OrderStatusForm from './components/OrderStatusForm.vue';
import type { OrderStatusFormData } from './components/OrderStatusForm.vue';

const form = useForm<OrderStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: OrderStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(orderStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Order Status
            </h1>
            <OrderStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Order Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
