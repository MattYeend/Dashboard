<script setup lang="ts">
import { formatDateTime } from '@/lib/campaigns';
import type { Campaign } from '@/types';

interface Props {
    campaign: Campaign;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Schedule</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Scheduled for</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaign.scheduled_at, 'Not scheduled') }}
                </dd>
            </div>
        </dl>
    </div>
</template>