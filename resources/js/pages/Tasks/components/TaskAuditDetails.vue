<script setup lang="ts">
import type { Task } from '@/types';

interface Props {
    task: Task;
}

defineProps<Props>();

function formatDateTime(value: string | null): string {
    if (!value) {
        return '—';
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
                <dd class="text-sm">{{ task.creator?.name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Created at</dt>
                <dd class="text-sm">{{ formatDateTime(task.created_at) }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Last updated by</dt>
                <dd class="text-sm">{{ task.updater?.name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last updated at</dt>
                <dd class="text-sm">{{ formatDateTime(task.updated_at) }}</dd>
            </div>

            <template v-if="task.deleted_at">
                <div>
                    <dt class="text-xs text-gray-400">Deleted by</dt>
                    <dd class="text-sm">{{ task.deleter?.name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Deleted at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(task.deleted_at) }}
                    </dd>
                </div>
            </template>

            <template v-if="task.restored_at">
                <div>
                    <dt class="text-xs text-gray-400">Restored by</dt>
                    <dd class="text-sm">{{ task.restorer?.name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Restored at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(task.restored_at) }}
                    </dd>
                </div>
            </template>
        </dl>
    </div>
</template>
