<script setup lang="ts">
import type { Invoice } from '@/types';

interface Props {
    invoice: Invoice;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Invoice number</dt>
                <dd class="text-sm">{{ invoice.invoice_number }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Company</dt>
                <dd class="text-sm">{{ invoice.company?.name ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span
                        v-if="invoice.status"
                        :style="{
                            backgroundColor:
                                invoice.status.background_colour ?? '#e2e8f0',
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
</template>
