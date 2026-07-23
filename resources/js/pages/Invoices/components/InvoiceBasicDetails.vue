<script setup lang="ts">
import type { Invoice } from '@/types';

defineProps<{
    invoice: Invoice;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Basic Details
            </h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">
                        Invoice Number
                    </dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ invoice.invoice_number }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Company</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ invoice.company?.name ?? '-' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Status</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        <span
                            v-if="invoice.status"
                            :style="{
                                backgroundColor:
                                    invoice.status.background_colour ??
                                    '#e2e8f0',
                                color: invoice.status.text_colour ?? '#1a202c',
                            }"
                            class="rounded px-2 py-0.5 text-xs font-medium"
                        >
                            {{ invoice.status.title }}
                        </span>
                        <span v-else>-</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
