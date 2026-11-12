<script setup lang="ts">
import type { Quote } from '@/types';

import QuoteStatusBadge from './QuoteStatusBadge.vue';

defineProps<{
    quote: Quote;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Quote number</dt>
            <dd class="font-medium">{{ quote.quote_number }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Status</dt>
            <dd><QuoteStatusBadge :status="quote.status" /></dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm text-muted-foreground">Title</dt>
            <dd class="font-medium">{{ quote.title }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm text-muted-foreground">Description</dt>
            <dd class="whitespace-pre-line">{{ quote.description ?? 'None' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm text-muted-foreground">Internal notes</dt>
            <dd class="whitespace-pre-line">{{ quote.notes ?? 'None' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm text-muted-foreground">Terms</dt>
            <dd class="whitespace-pre-line">{{ quote.terms ?? 'None' }}</dd>
        </div>
    </dl>
</template>