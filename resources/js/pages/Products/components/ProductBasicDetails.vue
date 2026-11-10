<script setup lang="ts">
import type { Product } from '@/types';

defineProps<{
    product: Product;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">SKU</dt>
            <dd class="font-medium">{{ product.sku }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Name</dt>
            <dd class="font-medium">{{ product.name }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm text-muted-foreground">Description</dt>
            <dd class="whitespace-pre-line">{{ product.description ?? 'None' }}</dd>
        </div>
    </dl>
</template>