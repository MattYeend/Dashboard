<script setup lang="ts">
import type { Product } from '@/types';

defineProps<{
    product: Product;
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
                {{ formatDate(product.created_at) }} by
                {{ product.creator?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Last updated</dt>
            <dd>
                {{ formatDate(product.updated_at) }} by
                {{ product.updater?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="product.deleted_at">
            <dt class="text-sm text-muted-foreground">Deleted</dt>
            <dd>
                {{ formatDate(product.deleted_at) }} by
                {{ product.deleter?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="product.restored_at">
            <dt class="text-sm text-muted-foreground">Restored</dt>
            <dd>
                {{ formatDate(product.restored_at) }} by
                {{ product.restorer?.name ?? 'Unknown' }}
            </dd>
        </div>
    </dl>
</template>
