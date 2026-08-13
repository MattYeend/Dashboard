<script setup lang="ts">
import type { Ticket } from '@/types';

interface Props {
    ticket: Ticket;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4">
            <div>
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm">
                    {{ ticket.title }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">
                    {{ ticket.description ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
