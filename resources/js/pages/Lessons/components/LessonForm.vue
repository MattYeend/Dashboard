<script setup lang="ts">
import LessonBasicDetailsForm from '@/pages/Lessons/components/LessonBasicDetailsForm.vue';
import LessonContentForm from '@/pages/Lessons/components/LessonContentForm.vue';
import LessonSettingsForm from '@/pages/Lessons/components/LessonSettingsForm.vue';
import type { CourseOption } from '@/types';
 
defineProps<{
    errors: Partial<Record<string, string>>;
    courses: CourseOption[];
}>();
 
const courseId = defineModel<number | null>('courseId', { required: true });
const title = defineModel<string>('title', { required: true });
const summary = defineModel<string>('summary', { required: true });
const content = defineModel<string>('content', { required: true });
const videoUrl = defineModel<string>('videoUrl', { required: true });
const durationMinutes = defineModel<number | null>('durationMinutes', { required: true });
const isPreview = defineModel<boolean>('isPreview', { required: true });
const publishedAt = defineModel<string>('publishedAt', { required: true });
</script>
 
<template>
    <div class="grid gap-6">
        <LessonBasicDetailsForm
            v-model:course-id="courseId"
            v-model:title="title"
            v-model:summary="summary"
            :errors="errors"
            :courses="courses"
        />
        <LessonContentForm v-model:content="content" v-model:video-url="videoUrl" :errors="errors" />
        <LessonSettingsForm
            v-model:duration-minutes="durationMinutes"
            v-model:is-preview="isPreview"
            v-model:published-at="publishedAt"
            :errors="errors"
        />
    </div>
</template>