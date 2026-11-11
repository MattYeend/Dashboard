<script setup lang="ts">
import type { QuoteStatus } from '@/types';

defineProps<{
    quoteStatus: QuoteStatus;
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
                {{ formatDate(quoteStatus.created_at) }} by
                {{ quoteStatus.creator?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Last updated</dt>
            <dd>
                {{ formatDate(quoteStatus.updated_at) }} by
                {{ quoteStatus.updater?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="quoteStatus.deleted_at">
            <dt class="text-sm text-muted-foreground">Deleted</dt>
            <dd>
                {{ formatDate(quoteStatus.deleted_at) }} by
                {{ quoteStatus.deleter?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="quoteStatus.restored_at">
            <dt class="text-sm text-muted-foreground">Restored</dt>
            <dd>
                {{ formatDate(quoteStatus.restored_at) }} by
                {{ quoteStatus.restorer?.name ?? 'Unknown' }}
            </dd>
        </div>
    </dl>
</template>
