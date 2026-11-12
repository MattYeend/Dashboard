<script setup lang="ts">
import type { Quote } from '@/types';

defineProps<{
    quote: Quote;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Customer type</dt>
            <dd>{{ quote.quotable_type_label ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Customer</dt>
            <dd class="font-medium">{{ quote.quotable_name ?? 'Unknown' }}</dd>
        </div>
    </dl>
</template>