<script setup lang="ts">
import type { CampaignSegment } from '@/types';
 
interface Props {
    campaignSegment: CampaignSegment;
}
 
defineProps<Props>();
 
function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Never';
    }
 
    return new Date(value).toLocaleString();
}
</script>
 
<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Audience</h2>
 
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Estimated recipients</dt>
                <dd class="text-sm">
                    {{ campaignSegment.estimated_recipients_count ?? 0 }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last refreshed</dt>
                <dd class="text-sm">
                    {{ formatDateTime(campaignSegment.last_refreshed_at) }}
                </dd>
            </div>
        </dl>
    </div>
</template>