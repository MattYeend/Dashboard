<script setup lang="ts">
import { formatDate, formatMoney } from '@/lib/money';
import type { Payment } from '@/types';

defineProps<{
    payment: Payment;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-muted-foreground">Type</dt>
            <dd class="font-medium">{{ payment.type === 'refund' ? 'Refund' : 'Payment' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Status</dt>
            <dd>
                <span
                    v-if="payment.status"
                    class="inline-block rounded-md border-2 px-3 py-1 text-sm font-medium"
                    :style="{
                        color: payment.status.text_colour,
                        borderColor: payment.status.background_colour,
                    }"
                >
                    {{ payment.status.title }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Amount</dt>
            <dd class="font-medium">
                {{ payment.type === 'refund' ? '-' : '' }}{{ formatMoney(payment.amount, payment.currency) }}
            </dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Method</dt>
            <dd>{{ payment.method_label }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Date received</dt>
            <dd>{{ formatDate(payment.paid_at) }}</dd>
        </div>
        <div v-if="payment.invoice">
            <dt class="text-sm text-muted-foreground">Invoice</dt>
            <dd>{{ payment.invoice.invoice_number }}</dd>
        </div>
    </dl>
</template>