<script setup lang="ts">
import type { Product } from '@/types';

defineProps<{
    product: Product;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Unit price</dt>
            <dd class="font-medium">{{ product.formatted_unit_price }} per {{ product.unit }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Cost price</dt>
            <dd>{{ product.cost_price ?? 'Not set' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Tax rate</dt>
            <dd>{{ product.tax_rate }}%</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Currency</dt>
            <dd>{{ product.currency }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Availability</dt>
            <dd>{{ product.is_active ? 'Active' : 'Inactive' }}</dd>
        </div>
    </dl>
</template>