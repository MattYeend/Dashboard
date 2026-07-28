<script setup lang="ts">
import type { Order } from '@/types';

interface Props {
    order: Order;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Order number</dt>
                <dd class="text-sm">{{ order.order_number }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm">{{ order.title }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span
                        v-if="order.status"
                        :style="{
                            backgroundColor:
                                order.status.background_colour ?? '#e2e8f0',
                            color: order.status.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ order.status.title }}
                    </span>
                    <span v-else>-</span>
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">{{ order.description ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Notes</dt>
                <dd class="text-sm">{{ order.notes ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
