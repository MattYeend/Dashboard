<script setup lang="ts">
import type { Campaign } from '@/types';

interface Props {
    campaign: Campaign;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">{{ campaign.name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Email template</dt>
                <dd class="text-sm">{{ campaign.template?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Segment</dt>
                <dd class="text-sm">{{ campaign.segment?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Owner</dt>
                <dd class="text-sm">{{ campaign.owner?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Subject override</dt>
                <dd class="text-sm">{{ campaign.subject ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
