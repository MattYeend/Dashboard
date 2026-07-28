<script setup lang="ts">
import type { InvoiceItem } from '@/types';

interface Props {
    item: InvoiceItem;
}

defineProps<Props>();

function formatMoney(pence: number): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(pence / 100);
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">{{ item.description }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Quantity</dt>
                <dd class="text-sm">{{ item.quantity }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Unit price</dt>
                <dd class="text-sm">{{ formatMoney(item.unit_price) }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Tax rate</dt>
                <dd class="text-sm">{{ item.tax_rate }}%</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Total</dt>
                <dd class="text-sm font-semibold">
                    {{ formatMoney(item.total) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Position</dt>
                <dd class="text-sm">{{ item.position }}</dd>
            </div>
        </dl>
    </div>
</template>