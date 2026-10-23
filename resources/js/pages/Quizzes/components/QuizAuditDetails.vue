<script setup lang="ts">
import type { Quiz } from '@/types';

defineProps<{
    quiz: Quiz;
}>();

/**
 * Format an ISO date string for display, or return a dash when empty.
 */
function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleString('en-GB') : '-';
}
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs text-gray-400">Created by</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.creator?.name ?? '-' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Created at</dt>
            <dd class="text-sm text-gray-300">
                {{ formatDate(quiz.created_at) }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Updated by</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.updater?.name ?? '-' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Updated at</dt>
            <dd class="text-sm text-gray-300">
                {{ formatDate(quiz.updated_at) }}
            </dd>
        </div>
        <div v-if="quiz.deleted_at">
            <dt class="text-xs text-gray-400">Deleted by</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.deleter?.name ?? '-' }}
            </dd>
        </div>
        <div v-if="quiz.deleted_at">
            <dt class="text-xs text-gray-400">Deleted at</dt>
            <dd class="text-sm text-gray-300">
                {{ formatDate(quiz.deleted_at) }}
            </dd>
        </div>
        <div v-if="quiz.restored_at">
            <dt class="text-xs text-gray-400">Restored by</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.restorer?.name ?? '-' }}
            </dd>
        </div>
        <div v-if="quiz.restored_at">
            <dt class="text-xs text-gray-400">Restored at</dt>
            <dd class="text-sm text-gray-300">
                {{ formatDate(quiz.restored_at) }}
            </dd>
        </div>
    </dl>
</template>
