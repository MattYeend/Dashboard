<script setup lang="ts">
import type { Ticket } from '@/types';

interface Props {
    ticket: Ticket;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">Labels</h3>
        </div>
        <div class="border-t border-gray-500">
            <div
                v-if="ticket.labels?.length"
                class="flex flex-wrap gap-2 px-4 py-3 sm:px-6"
            >
                <span
                    v-for="label in ticket.labels"
                    :key="label.id"
                    class="inline-block rounded border px-2 py-0.5 text-xs font-medium"
                >
                    {{ label.name }}
                </span>
            </div>
            <p v-else class="px-4 py-5 text-sm text-gray-400 sm:px-6">
                No labels have been attached to this ticket yet.
            </p>
        </div>
    </div>
</template>
