<script setup lang="ts">
import type { Quiz } from '@/types';

defineProps<{
    quiz: Quiz;
}>();
</script>

<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs text-gray-400">Course</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.course?.title ?? 'None' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Lesson</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.lesson?.title ?? 'Whole course' }}
            </dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Title</dt>
            <dd class="text-sm text-gray-300">{{ quiz.title }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs text-gray-400">Description</dt>
            <dd class="text-sm whitespace-pre-line text-gray-300">
                {{ quiz.description ?? 'None' }}
            </dd>
        </div>
    </dl>
</template>
