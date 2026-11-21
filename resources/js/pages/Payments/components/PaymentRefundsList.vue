<script setup lang="ts">
import { formatDate, formatMoney } from '@/lib/money';
import type { Payment } from '@/types';

defineProps<{
    refunds: Payment[];
}>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <p v-if="refunds.length === 0" class="text-sm text-muted-foreground">
            No refunds recorded.
        </p>

        <ul v-else class="grid gap-2">
            <li
                v-for="refund in refunds"
                :key="refund.id"
                class="flex items-center justify-between gap-4"
            >
                <div>
                    <p class="text-sm font-medium">
                        Refund {{ formatDate(refund.paid_at) }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ refund.notes }}
                    </p>
                </div>
                <span class="text-sm font-medium"
                    >-{{ formatMoney(refund.amount, refund.currency) }}</span
                >
            </li>
        </ul>
    </div>
</template>
