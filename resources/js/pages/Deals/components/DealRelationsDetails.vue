<script setup lang="ts">
import type { Deal } from '@/types';

interface Props {
    deal: Deal;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Related records</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Company</dt>
                <dd class="text-sm">{{ deal.company?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Invoice</dt>
                <dd class="text-sm">
                    {{ deal.invoice?.invoice_number ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
