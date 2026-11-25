<script setup lang="ts">
import type { EmailSuppression } from '@/types';

interface Props {
    emailSuppression: EmailSuppression;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Email</dt>
                <dd class="text-sm">{{ emailSuppression.email }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Reason</dt>
                <dd class="text-sm capitalize">
                    {{ emailSuppression.reason.replace(/_/g, ' ') }}
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Notes</dt>
                <dd class="text-sm whitespace-pre-line">
                    {{ emailSuppression.notes ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
