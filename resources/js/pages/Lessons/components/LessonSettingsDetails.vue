<script setup lang="ts">
import type { Lesson } from '@/types';

defineProps<{
    lesson: Lesson;
}>();
</script>

<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-3">
        <div>
            <dt class="text-xs text-gray-400">Duration</dt>
            <dd class="text-sm text-gray-300">
                {{
                    lesson.duration_minutes
                        ? `${lesson.duration_minutes} minutes`
                        : 'Not set'
                }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Free preview</dt>
            <dd class="text-sm text-gray-300">
                {{ lesson.is_preview ? 'Yes' : 'No' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Published</dt>
            <dd class="text-sm text-gray-300">
                {{
                    lesson.published_at
                        ? new Date(lesson.published_at).toLocaleDateString(
                              'en-GB',
                          )
                        : 'Draft'
                }}
            </dd>
        </div>
    </dl>
</template>
