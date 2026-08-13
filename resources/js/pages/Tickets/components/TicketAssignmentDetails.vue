<script setup lang="ts">
import type { Ticket } from '@/types';

interface Props {
    ticket: Ticket;
}

defineProps<Props>();

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString();
}

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">
            Assignment &amp; due date
        </h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-xs text-gray-400">Assigned to</dt>
                <dd class="text-sm">
                    {{ ticket.assignee?.name ?? 'Unassigned' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Due date</dt>
                <dd class="text-sm">
                    {{ formatDate(ticket.due_date) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Resolution</dt>
                <dd class="text-sm">
                    <span
                        v-if="ticket.resolved_at"
                        class="inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800"
                    >
                        Resolved
                    </span>
                    <span
                        v-else
                        class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                    >
                        Unresolved
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Resolved at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(ticket.resolved_at) }}
                </dd>
            </div>
        </dl>
    </div>
</template>