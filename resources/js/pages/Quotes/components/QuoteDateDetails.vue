<script setup lang="ts">
import type { Quote } from '@/types';

defineProps<{
    quote: Quote;
}>();

function show(value: string | null): string {
    return value ? value.replace('T', ' ') : 'Not set';
}
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Issued</dt>
            <dd>{{ show(quote.issued_at) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Valid until</dt>
            <dd>{{ show(quote.valid_until) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Sent</dt>
            <dd>{{ show(quote.sent_at) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Accepted</dt>
            <dd>{{ show(quote.accepted_at) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Declined</dt>
            <dd>{{ show(quote.declined_at) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Expired</dt>
            <dd>{{ show(quote.expired_at) }}</dd>
        </div>
        <div v-if="quote.converted_order">
            <dt class="text-sm text-muted-foreground">Converted to order</dt>
            <dd>{{ quote.converted_order.order_number }} on {{ show(quote.converted_at) }}</dd>
        </div>
    </dl>
</template>