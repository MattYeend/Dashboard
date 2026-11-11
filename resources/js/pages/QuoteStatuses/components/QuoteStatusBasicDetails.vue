<script setup lang="ts">
import type { QuoteStatus } from '@/types';

defineProps<{
    quoteStatus: QuoteStatus;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Title</dt>
            <dd class="font-medium">{{ quoteStatus.title }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Type</dt>
            <dd>{{ quoteStatus.is_system ? 'System status' : 'Custom status' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm text-muted-foreground">Description</dt>
            <dd class="whitespace-pre-line">{{ quoteStatus.description ?? 'None' }}</dd>
        </div>
    </dl>
</template>