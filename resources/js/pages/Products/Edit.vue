<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ProductForm from '@/pages/Products/components/ProductForm.vue';
import { show, update } from '@/routes/products';
import type { Product } from '@/types';
import type { ProductFormData } from '@/types/forms/products-form-data';

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

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Product</h1>

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
    </div>
</template>
