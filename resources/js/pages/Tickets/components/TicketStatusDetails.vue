<script setup lang="ts">
import type { Ticket } from '@/types';

interface Props {
    ticket: Ticket;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Status</h2>

        <dl class="grid grid-cols-1 gap-4">
            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span
                        v-if="ticket.status"
                        :style="{
                            backgroundColor:
                                ticket.status.background_colour ?? '#e2e8f0',
                            color: ticket.status.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ ticket.status.title }}
                    </span>
                    <span v-else>-</span>
                </dd>
            </div>
        </dl>
    </div>
</template>
