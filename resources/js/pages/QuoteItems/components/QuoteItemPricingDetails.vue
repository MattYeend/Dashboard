<script setup lang="ts">
import { formatDecimalMoney } from '@/lib/decimal-money';
import type { QuoteItem } from '@/types';

defineProps<{
    item: QuoteItem;
    currency: string;
}>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Pricing</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Quantity</dt>
                <dd class="text-sm">{{ item.quantity }} {{ item.unit }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Unit price</dt>
                <dd class="text-sm">
                    {{ formatDecimalMoney(item.unit_price, currency) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Discount</dt>
                <dd class="text-sm">{{ item.discount_percent }}%</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Tax rate</dt>
                <dd class="text-sm">{{ item.tax_rate }}%</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Line tax</dt>
                <dd class="text-sm">
                    {{ formatDecimalMoney(item.line_tax, currency) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Line total</dt>
                <dd class="text-sm font-semibold">
                    {{ formatDecimalMoney(item.line_total, currency) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Position</dt>
                <dd class="text-sm">{{ item.position }}</dd>
            </div>
        </dl>
    </div>
</template>
