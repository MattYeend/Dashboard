<script setup lang="ts">
import type { Course } from '@/types';
 
defineProps<{
    course: Course;
}>();
</script>
 
<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-4">
        <div>
            <dt class="text-xs text-gray-400">Difficulty</dt>
            <dd class="text-sm text-gray-300">{{ course.difficulty_label }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Duration</dt>
            <dd class="text-sm text-gray-300">
                {{ course.estimated_duration_minutes ? `${course.estimated_duration_minutes} minutes` : 'Not set' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Instructor</dt>
            <dd class="text-sm text-gray-300">{{ course.instructor?.name ?? 'Unassigned' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Published</dt>
            <dd class="text-sm text-gray-300">
                {{ course.published_at ? new Date(course.published_at).toLocaleDateString('en-GB') : 'Draft' }}
            </dd>
        </div>
    </dl>
</template>