<script setup lang="ts">
import type { Pipeline } from '@/types';

interface Props {
    pipeline: Pipeline;
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
        <h2 class="mb-4 text-sm font-medium text-gray-400">
            Audit details
        </h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Created by</dt>
                <dd class="text-sm">{{ pipeline.creator?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Created at</dt>
                <dd class="text-sm">{{ formatDateTime(pipeline.created_at) }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Last updated by</dt>
                <dd class="text-sm">{{ pipeline.updater?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last updated at</dt>
                <dd class="text-sm">{{ formatDateTime(pipeline.updated_at) }}</dd>
            </div>

            <template v-if="pipeline.deleted_at">
                <div>
                    <dt class="text-xs text-gray-400">Deleted by</dt>
                    <dd class="text-sm">{{ pipeline.deleter?.name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Deleted at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(pipeline.deleted_at) }}
                    </dd>
                </div>
            </template>

            <template v-if="pipeline.restored_at">
                <div>
                    <dt class="text-xs text-gray-400">Restored by</dt>
                    <dd class="text-sm">{{ pipeline.restorer?.name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Restored at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(pipeline.restored_at) }}
                    </dd>
                </div>
            </template>
        </dl>
    </div>
</template>
