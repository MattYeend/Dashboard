<script setup lang="ts">
import type { Course } from '@/types';

defineProps<{
    course: Course;
}>();
</script>

<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Title</dt>
            <dd class="text-sm text-gray-300">{{ course.title }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Summary</dt>
            <dd class="text-sm text-gray-300">
                {{ course.summary ?? 'None' }}
            </dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Description</dt>
            <dd class="text-sm whitespace-pre-line text-gray-300">
                {{ course.description ?? 'None' }}
            </dd>
        </div>
    </dl>
</template>
