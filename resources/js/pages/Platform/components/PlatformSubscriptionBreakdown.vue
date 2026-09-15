<script setup lang="ts">
import type { PlatformReportingMetrics } from '@/types';

defineProps<{
    subscriptionCounts: PlatformReportingMetrics['subscription_counts'];
}>();
</script>

<template>
    <div class="rounded-md border border-gray-500 p-4">
        <h3 class="mb-3 text-xs text-gray-400">Subscriptions</h3>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-xs text-gray-400">Active</dt>
                <dd class="text-xl text-gray-300">
                    {{ subscriptionCounts.active }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Trialing</dt>
                <dd class="text-xl text-gray-300">
                    {{ subscriptionCounts.trialing }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Cancelled</dt>
                <dd class="text-xl text-gray-300">
                    {{ subscriptionCounts.cancelled }}
                </dd>
            </div>
        </dl>
    </div>
</template>
