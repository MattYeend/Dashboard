<script setup lang="ts">
import LessonVideoEmbed from '@/pages/Lessons/components/LessonVideoEmbed.vue';
import type { Lesson } from '@/types';

defineProps<{
    lesson: Lesson;
}>();
</script>

<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4">
        <LessonVideoEmbed :embed-url="lesson.embed_url" :title="lesson.title" />
        <div>
            <p class="text-xs text-gray-400">Content</p>
            <p class="text-sm whitespace-pre-line text-gray-300">
                {{ lesson.content ?? 'None' }}
            </p>
        </div>
    </div>
</template>
