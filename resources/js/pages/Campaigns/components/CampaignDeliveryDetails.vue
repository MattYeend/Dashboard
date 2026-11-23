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
        <h2 class="mb-4 text-sm font-medium text-gray-400">Delivery</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-xs text-gray-400">Recipients</dt>
                <dd class="text-sm">{{ campaign.recipients_count ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Sent</dt>
                <dd class="text-sm">{{ campaign.sent_count ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Failed</dt>
                <dd class="text-sm">{{ campaign.failed_count ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Skipped</dt>
                <dd class="text-sm">{{ campaign.skipped_count ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Opened</dt>
                <dd class="text-sm">{{ campaign.opened_count ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Unsubscribed</dt>
                <dd class="text-sm">{{ campaign.unsubscribed_count ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Started</dt>
                <dd class="text-sm">{{ formatDateTime(campaign.started_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Completed</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaign.completed_at) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Cancelled</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaign.cancelled_at) }}
                </dd>
            </div>
        </dl>
    </div>
</template>