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
        <h2 class="mb-4 text-sm font-medium text-gray-400">Audit details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Created by</dt>
                <dd class="text-sm">
                    {{ emailSuppression.creator?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Created at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(emailSuppression.created_at) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Last updated by</dt>
                <dd class="text-sm">
                    {{ emailSuppression.updater?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last updated at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(emailSuppression.updated_at) }}
                </dd>
            </div>

            <template v-if="emailSuppression.deleted_at">
                <div>
                    <dt class="text-xs text-gray-400">Deleted by</dt>
                    <dd class="text-sm">
                        {{ emailSuppression.deleter?.name ?? 'System' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Deleted at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(emailSuppression.deleted_at) }}
                    </dd>
                </div>
            </template>

            <template v-if="emailSuppression.restored_at">
                <div>
                    <dt class="text-xs text-gray-400">Restored by</dt>
                    <dd class="text-sm">
                        {{ emailSuppression.restorer?.name ?? 'System' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Restored at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(emailSuppression.restored_at) }}
                    </dd>
                </div>
            </template>
        </dl>
    </div>
</template>