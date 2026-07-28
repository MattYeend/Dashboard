<script setup lang="ts">
import type { Invoice } from '@/types';

interface Props {
    invoice: Invoice;
}

defineProps<Props>();

function formatMoney(pence: number, currency: string): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(pence / 100);
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Totals</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Subtotal</dt>
                <dd class="text-sm">
                    {{ formatMoney(invoice.subtotal, invoice.currency) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Tax total</dt>
                <dd class="text-sm">
                    {{ formatMoney(invoice.tax_total, invoice.currency) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Total</dt>
                <dd class="text-sm font-semibold">
                    {{ formatMoney(invoice.total, invoice.currency) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Notes</dt>
                <dd class="text-sm">{{ invoice.notes ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
