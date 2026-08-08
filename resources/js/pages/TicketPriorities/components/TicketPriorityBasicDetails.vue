<script setup lang="ts">
import type { TicketPriority } from '@/types';

interface Props {
    ticketPriority: TicketPriority;
}

defineProps<Props>();

const levelLabels: Record<number, string> = {
    1: 'Low',
    2: 'Medium',
    3: 'High',
    4: 'Critical',
};
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm">
                    {{ ticketPriority.title }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Level</dt>
                <dd class="text-sm">
                    {{
                        levelLabels[ticketPriority.level] ??
                        ticketPriority.level
                    }}
                </dd>
            </div>
        </dl>
    </div>
</template>
