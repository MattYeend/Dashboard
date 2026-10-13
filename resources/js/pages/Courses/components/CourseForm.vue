<script setup lang="ts">
import CourseBasicDetailsForm from '@/pages/Courses/components/CourseBasicDetailsForm.vue';
import CoursePublishingForm from '@/pages/Courses/components/CoursePublishingForm.vue';
import CourseSettingsForm from '@/pages/Courses/components/CourseSettingsForm.vue';
import type { DifficultyOption, UserOption } from '@/types';

defineProps<{
    errors: Partial<Record<string, string>>;
    difficulties: DifficultyOption[];
    instructors: UserOption[];
}>();

const title = defineModel<string>('title', { required: true });
const summary = defineModel<string>('summary', { required: true });
const description = defineModel<string>('description', { required: true });
const difficulty = defineModel<string>('difficulty', { required: true });
const estimatedDurationMinutes = defineModel<number | null>(
    'estimatedDurationMinutes',
    { required: true },
);
const instructorId = defineModel<number | null>('instructorId', {
    required: true,
});
const publishedAt = defineModel<string>('publishedAt', { required: true });
</script>

<template>
    <div class="grid gap-6">
        <CourseBasicDetailsForm
            v-model:title="title"
            v-model:summary="summary"
            v-model:description="description"
            :errors="errors"
        />
        <CourseSettingsForm
            v-model:difficulty="difficulty"
            v-model:estimated-duration-minutes="estimatedDurationMinutes"
            v-model:instructor-id="instructorId"
            :errors="errors"
            :difficulties="difficulties"
            :instructors="instructors"
        />
        <CoursePublishingForm
            v-model:published-at="publishedAt"
            :errors="errors"
        />
    </div>
</template>
