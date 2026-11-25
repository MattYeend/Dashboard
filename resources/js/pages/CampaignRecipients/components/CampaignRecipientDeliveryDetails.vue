<script setup lang="ts">
import { formatDateTime, humaniseStatus } from '@/lib/campaign-recipients';
import type { CampaignRecipient } from '@/types';

interface Props {
    campaignRecipient: CampaignRecipient;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Delivery</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    {{ humaniseStatus(campaignRecipient.status) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Attempts</dt>
                <dd class="text-sm">{{ campaignRecipient.attempts ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Sent</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaignRecipient.sent_at, 'Not sent') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Opened</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaignRecipient.opened_at, 'Never') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Clicked</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaignRecipient.clicked_at, 'Never') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Unsubscribed</dt>
                <dd class="text-sm">
                    {{
                        formatDateTime(
                            campaignRecipient.unsubscribed_at,
                            'Never',
                        )
                    }}
                </dd>
            </div>

            <template v-if="campaignRecipient.failed_at">
                <div>
                    <dt class="text-xs text-gray-400">Failed</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(campaignRecipient.failed_at) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Failure reason</dt>
                    <dd class="text-sm">
                        {{ campaignRecipient.failure_reason ?? '-' }}
                    </dd>
                </div>
            </template>
        </dl>
    </div>
</template>