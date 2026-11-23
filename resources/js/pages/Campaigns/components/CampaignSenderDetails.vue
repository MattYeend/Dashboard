<script setup lang="ts">
import type { Campaign } from '@/types';

interface Props {
    campaign: Campaign;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Sender</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">From name</dt>
                <dd class="text-sm">{{ campaign.from_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Reply-to address</dt>
                <dd class="text-sm">{{ campaign.reply_to ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
