<script setup lang="ts">
import type { CampaignRecipient } from '@/types';

interface Props {
    campaignRecipient: CampaignRecipient;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Email</dt>
                <dd class="text-sm">{{ campaignRecipient.email }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">{{ campaignRecipient.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Campaign</dt>
                <dd class="text-sm">
                    {{ campaignRecipient.campaign?.name ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Contact</dt>
                <dd class="text-sm">
                    {{ campaignRecipient.contact?.title ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>