<script setup lang="ts">
import type { PaymentStatus } from '@/types';

defineProps<{
    paymentStatus: PaymentStatus;
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
                {{ formatDate(paymentStatus.created_at) }} by
                {{ paymentStatus.creator?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Last updated</dt>
            <dd>
                {{ formatDate(paymentStatus.updated_at) }} by
                {{ paymentStatus.updater?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="paymentStatus.deleted_at">
            <dt class="text-sm text-muted-foreground">Deleted</dt>
            <dd>
                {{ formatDate(paymentStatus.deleted_at) }} by
                {{ paymentStatus.deleter?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="paymentStatus.restored_at">
            <dt class="text-sm text-muted-foreground">Restored</dt>
            <dd>
                {{ formatDate(paymentStatus.restored_at) }} by
                {{ paymentStatus.restorer?.name ?? 'Unknown' }}
            </dd>
        </div>
    </dl>
</template>
