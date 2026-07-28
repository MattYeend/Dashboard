<script setup lang="ts">
import type { Plan } from '@/types';

interface Props {
    plan: Plan;
}

defineProps<Props>();

function formatPrice(pence: number): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(pence / 100);
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Pricing</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Price per user, per month</dt>
                <dd class="text-sm">
                    {{ formatPrice(plan.price_per_user_per_month) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Active</dt>
                <dd class="text-sm">
                    {{ plan.is_active ? 'Yes' : 'No' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
