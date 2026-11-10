<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import ProductAuditDetails from '@/pages/Products/components/ProductAuditDetails.vue';
import ProductBasicDetails from '@/pages/Products/components/ProductBasicDetails.vue';
import ProductPricingDetails from '@/pages/Products/components/ProductPricingDetails.vue';
import {
    destroy as productsDestroy,
    edit as productsEdit,
    index as productsIndex,
} from '@/routes/products';
import type { PermissionsMeta, Product } from '@/types';

interface Props {
    product: Product;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(productsDestroy.url(props.product.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <Head :title="product.name" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ product.name }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="productsIndex.url()">Back</Link>
                    </Button>
                    <template v-if="!product.deleted_at">
                        <Button as-child>
                            <Link :href="productsEdit.url(product.id)"
                                >Edit</Link
                            >
                        </Button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                            @click="requestDestroy"
                        >
                            Delete
                        </button>
                    </template>
                </div>
            </div>

            <div class="space-y-6">
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
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete product"
            description="This product will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
