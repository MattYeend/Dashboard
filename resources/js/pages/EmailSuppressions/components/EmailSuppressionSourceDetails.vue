<script setup lang="ts">
import type { EmailSuppression } from '@/types';

interface Props {
    emailSuppression: EmailSuppression;
}

defineProps<Props>();

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Source</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Suppressed at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(emailSuppression.suppressed_at) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Campaign</dt>
                <dd class="text-sm">
                    {{ emailSuppression.campaign?.name ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Contact</dt>
                <dd class="text-sm">
                    {{ emailSuppression.contact?.title ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
