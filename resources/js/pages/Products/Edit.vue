<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, update } from '@/routes/products';
import type { Product } from '@/types';

import ProductForm from './components/ProductForm.vue';
import type { ProductFormData } from '@/types/forms/products';

const props = defineProps<{
    product: Product;
    units: string[];
    currencies: string[];
}>();

const form = useForm<ProductFormData>({
    sku: props.product.sku,
    name: props.product.name,
    description: props.product.description,
    unit: props.product.unit,
    unit_price: props.product.unit_price,
    cost_price: props.product.cost_price,
    tax_rate: props.product.tax_rate,
    currency: props.product.currency,
    is_active: props.product.is_active,
});

function submit(): void {
    form.put(update.url(props.product.id));
}
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Products', href: index.url() },
            { title: product.name, href: show.url(product.id) },
            { title: 'Edit', href: '#' },
        ]"
    >
        <div class="mx-auto w-full max-w-4xl p-4">
            <h1 class="mb-6 text-2xl font-semibold">Edit product</h1>

            <ProductForm
                v-model="form"
                :errors="form.errors"
                :processing="form.processing"
                :units="units"
                :currencies="currencies"
                submit-label="Save changes"
                :cancel-href="show.url(product.id)"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>