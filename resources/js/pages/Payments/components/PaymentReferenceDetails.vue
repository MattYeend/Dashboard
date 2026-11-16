<script setup lang="ts">
import type { Payment } from '@/types';

defineProps<{
    payment: Payment;
}>();
</script>

<template>
    <dl class="grid gap-4">
        <div>
            <dt class="text-sm text-muted-foreground">Reference</dt>
            <dd>{{ payment.reference ?? 'None' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Notes</dt>
            <dd class="whitespace-pre-line">{{ payment.notes ?? 'None' }}</dd>
        </div>
    </dl>
</template>