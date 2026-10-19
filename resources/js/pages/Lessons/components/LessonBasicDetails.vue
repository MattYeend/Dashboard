<script setup lang="ts">
import type { Lesson } from '@/types';
 
defineProps<{
    lesson: Lesson;
}>();
</script>
 
<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs text-gray-400">Course</dt>
            <dd class="text-sm text-gray-300">{{ lesson.course?.title ?? 'None' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Position</dt>
            <dd class="text-sm text-gray-300">{{ lesson.position }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Title</dt>
            <dd class="text-sm text-gray-300">{{ lesson.title }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Summary</dt>
            <dd class="text-sm text-gray-300">{{ lesson.summary ?? 'None' }}</dd>
        </div>
    </dl>
</template>