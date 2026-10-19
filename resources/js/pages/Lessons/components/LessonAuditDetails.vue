<script setup lang="ts">
import type { Lesson } from '@/types';

defineProps<{
    lesson: Lesson;
}>();

/**
 * Format a date string for display in en-GB.
 */
function formatDate(value: string | null): string {
    return value
        ? new Date(value).toLocaleDateString('en-GB', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : 'N/A';
}
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-4">
        <div>
            <dt class="text-xs text-gray-400">Created by</dt>
            <dd class="text-sm text-gray-300">
                {{ lesson.creator?.name ?? 'System' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Updated by</dt>
            <dd class="text-sm text-gray-300">
                {{ lesson.updater?.name ?? 'System' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Deleted by</dt>
            <dd class="text-sm text-gray-300">
                {{ lesson.deleter?.name ?? 'System' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Restored by</dt>
            <dd class="text-sm text-gray-300">
                {{ lesson.restorer?.name ?? 'System' }} ({{
                    formatDate(lesson.restored_at)
                }})
            </dd>
        </div>
    </dl>
</template>
