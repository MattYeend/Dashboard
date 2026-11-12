<script setup lang="ts">
import type { Quote } from '@/types';

defineProps<{
    quote: Quote;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Subtotal</dt>
            <dd>{{ quote.currency }} {{ quote.subtotal }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Discount</dt>
            <dd>{{ quote.currency }} {{ quote.discount_amount }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Tax</dt>
            <dd>{{ quote.currency }} {{ quote.tax_amount }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Total</dt>
            <dd class="font-semibold">{{ quote.formatted_total }}</dd>
        </div>
    </dl>
</template>