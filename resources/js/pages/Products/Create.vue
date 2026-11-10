<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { index, store } from '@/routes/products';

import ProductForm from './components/ProductForm.vue';
import type { ProductFormData } from '@/types/forms/products';

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

    <AppLayout :breadcrumbs="[{ title: 'Products', href: index.url() }, { title: 'Create', href: '#' }]">
        <div class="mx-auto w-full max-w-4xl p-4">
            <h1 class="mb-6 text-2xl font-semibold">Create product</h1>

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
    </AppLayout>
</template>