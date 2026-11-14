<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ProductForm from '@/pages/Products/components/ProductForm.vue';
import { index, store } from '@/routes/products';
import type { ProductFormData } from '@/types/forms/products-form-data';

defineProps<{
    units: string[];
    currencies: string[];
}>();

const form = useForm<ProductFormData>({
    sku: '',
    name: '',
    description: null,
    unit: 'each',
    unit_price: '0.00',
    cost_price: null,
    tax_rate: '20.00',
    currency: 'GBP',
    is_active: true,
});

function submit(): void {
    form.post(store.url());
}
</script>

<template>
    <Head title="Create product" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Product</h1>

            <ProductForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :units="units"
                :currencies="currencies"
                submit-label="Create product"
                :cancel-href="index.url()"
                @submit="submit"
            />
        </div>
    </div>
</template>
