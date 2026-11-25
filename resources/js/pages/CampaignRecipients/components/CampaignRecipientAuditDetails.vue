<script setup lang="ts">
import { formatDateTime } from '@/lib/campaign-recipients';
import type { CampaignRecipient } from '@/types';

interface Props {
    campaignRecipient: CampaignRecipient;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Audit details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Created by</dt>
                <dd class="text-sm">
                    {{ campaignRecipient.creator?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Created at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaignRecipient.created_at) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Last updated by</dt>
                <dd class="text-sm">
                    {{ campaignRecipient.updater?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last updated at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaignRecipient.updated_at) }}
                </dd>
            </div>

            <template v-if="campaignRecipient.deleted_at">
                <div>
                    <dt class="text-xs text-gray-400">Deleted by</dt>
                    <dd class="text-sm">
                        {{ campaignRecipient.deleter?.name ?? 'System' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Deleted at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(campaignRecipient.deleted_at) }}
                    </dd>
                </div>
            </template>

            <template v-if="campaignRecipient.restored_at">
                <div>
                    <dt class="text-xs text-gray-400">Restored by</dt>
                    <dd class="text-sm">
                        {{ campaignRecipient.restorer?.name ?? 'System' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Restored at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(campaignRecipient.restored_at) }}
                    </dd>
                </div>
            </template>
        </dl>
    </div>
</template>