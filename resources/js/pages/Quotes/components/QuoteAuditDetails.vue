<script setup lang="ts">
import type { Quote } from '@/types';

defineProps<{
    quote: Quote;
}>();

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleString('en-GB') : 'Never';
}
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Created</dt>
            <dd>
                {{ formatDate(quote.created_at) }} by
                {{ quote.creator?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Last updated</dt>
            <dd>
                {{ formatDate(quote.updated_at) }} by
                {{ quote.updater?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="quote.deleted_at">
            <dt class="text-sm text-muted-foreground">Deleted</dt>
            <dd>
                {{ formatDate(quote.deleted_at) }} by
                {{ quote.deleter?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="quote.restored_at">
            <dt class="text-sm text-muted-foreground">Restored</dt>
            <dd>
                {{ formatDate(quote.restored_at) }} by
                {{ quote.restorer?.name ?? 'Unknown' }}
            </dd>
        </div>
    </dl>
</template>
