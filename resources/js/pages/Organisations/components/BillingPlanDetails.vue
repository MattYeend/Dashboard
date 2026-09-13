<script setup lang="ts">
import type { Plan } from '@/types';

defineProps<{
    plan: Plan;
}>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Current plan</h2>

        <dl class="space-y-4">
            <div>
                <dt class="text-xs font-medium text-gray-400">Plan</dt>
                <dd class="mt-1 text-sm text-gray-300">{{ plan.name }}</dd>
            </div>

            <div v-if="plan.description">
                <dt class="text-xs font-medium text-gray-400">Description</dt>
                <dd class="mt-1 text-sm text-gray-300">
                    {{ plan.description }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400">
                    Price per seat
                </dt>
                <dd class="mt-1 text-sm text-gray-300">
                    {{ (plan.price_per_user_per_month / 100).toFixed(2) }} per
                    user / month
                </dd>
            </div>
        </dl>
    </div>
</template>
