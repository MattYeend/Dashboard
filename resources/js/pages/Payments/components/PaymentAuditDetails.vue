<script setup lang="ts">
import type { Payment } from '@/types';

defineProps<{
    payment: Payment;
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
                {{ formatDate(payment.created_at) }} by
                {{ payment.creator?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Last updated</dt>
            <dd>
                {{ formatDate(payment.updated_at) }} by
                {{ payment.updater?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="payment.deleted_at">
            <dt class="text-sm text-muted-foreground">Deleted</dt>
            <dd>
                {{ formatDate(payment.deleted_at) }} by
                {{ payment.deleter?.name ?? 'Unknown' }}
            </dd>
        </div>
        <div v-if="payment.restored_at">
            <dt class="text-sm text-muted-foreground">Restored</dt>
            <dd>
                {{ formatDate(payment.restored_at) }} by
                {{ payment.restorer?.name ?? 'Unknown' }}
            </dd>
        </div>
    </dl>
</template>
