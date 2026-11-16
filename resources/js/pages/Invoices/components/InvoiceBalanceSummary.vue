<script setup lang="ts">
import { formatMoney } from '@/lib/money';
import type { Invoice } from '@/types';

defineProps<{
    invoice: Invoice;
}>();
</script>

<template>
    <dl class="grid gap-4 md:grid-cols-3">
        <div>
            <dt class="text-sm text-muted-foreground">Invoice total</dt>
            <dd class="font-medium">{{ invoice.formatted_total }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Paid</dt>
            <dd class="font-medium">{{ formatMoney(invoice.amount_paid) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Outstanding</dt>
            <dd class="font-medium">{{ formatMoney(invoice.balance_due) }}</dd>
        </div>
    </dl>
</template>