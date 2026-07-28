<script setup lang="ts">
import type { Invoice } from '@/types';

interface Props {
    invoice: Invoice;
}

defineProps<Props>();

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Dates</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Issue date</dt>
                <dd class="text-sm">
                    {{ formatDateTime(invoice.issue_date) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Due date</dt>
                <dd class="text-sm">{{ formatDateTime(invoice.due_date) }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Sent at</dt>
                <dd class="text-sm">{{ formatDateTime(invoice.sent_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Paid at</dt>
                <dd class="text-sm">{{ formatDateTime(invoice.paid_at) }}</dd>
            </div>
        </dl>
    </div>
</template>
