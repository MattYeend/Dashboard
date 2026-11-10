<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, index } from '@/routes/products';
import type { Product } from '@/types';

import ProductAuditDetails from './components/ProductAuditDetails.vue';
import ProductBasicDetails from './components/ProductBasicDetails.vue';
import ProductPricingDetails from './components/ProductPricingDetails.vue';

defineProps<{
    product: Product;
    permissions_meta: { can_create: boolean; can_view_any: boolean; can_export: boolean };
}>();
</script>

<template>
    <Head :title="product.name" />

    <AppLayout :breadcrumbs="[{ title: 'Products', href: index.url() }, { title: product.name, href: '#' }]">
        <div class="mx-auto grid w-full max-w-4xl gap-8 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ product.name }}</h1>
                <Button v-if="!product.deleted_at" as-child>
                    <Link :href="edit.url(product.id)">Edit</Link>
                </Button>
            </div>

            <section class="grid gap-4">
                <h2 class="text-lg font-semibold">Basic details</h2>
                <ProductBasicDetails :product="product" />
            </section>

            <section class="grid gap-4">
                <h2 class="text-lg font-semibold">Pricing</h2>
                <ProductPricingDetails :product="product" />
            </section>

            <section class="grid gap-4">
                <h2 class="text-lg font-semibold">Audit</h2>
                <ProductAuditDetails :product="product" />
            </section>
        </div>
    </AppLayout>
</template>