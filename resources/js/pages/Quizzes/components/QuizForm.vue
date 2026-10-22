<script setup lang="ts">
import QuizBasicDetailsForm from '@/pages/Quizzes/components/QuizBasicDetailsForm.vue';
import QuizPublishingForm from '@/pages/Quizzes/components/QuizPublishingForm.vue';
import type { QuizQuestionSelection } from '@/pages/Quizzes/components/QuizQuestionsForm.vue';
import QuizQuestionsForm from '@/pages/Quizzes/components/QuizQuestionsForm.vue';
import QuizSettingsForm from '@/pages/Quizzes/components/QuizSettingsForm.vue';
import type { CourseOption, QuestionOption } from '@/types';

defineProps<{
    errors: Partial<Record<string, string>>;
    courses: CourseOption[];
    questionOptions: QuestionOption[];
}>();

const courseId = defineModel<number | null>('courseId', { required: true });
const lessonId = defineModel<number | null>('lessonId', { required: true });
const title = defineModel<string>('title', { required: true });
const description = defineModel<string>('description', { required: true });
const passMarkPercentage = defineModel<number>('passMarkPercentage', {
    required: true,
});
const timeLimitMinutes = defineModel<number | null>('timeLimitMinutes', {
    required: true,
});
const maxAttempts = defineModel<number | null>('maxAttempts', {
    required: true,
});
const shuffleQuestions = defineModel<boolean>('shuffleQuestions', {
    required: true,
});
const showCorrectAnswers = defineModel<boolean>('showCorrectAnswers', {
    required: true,
});
const publishedAt = defineModel<string>('publishedAt', { required: true });
const questions = defineModel<QuizQuestionSelection[]>('questions', {
    required: true,
});
</script>

<template>
    <div class="grid gap-6">
        <QuizBasicDetailsForm
            v-model:course-id="courseId"
            v-model:lesson-id="lessonId"
            v-model:title="title"
            v-model:description="description"
            :errors="errors"
            :courses="courses"
        />
        <QuizSettingsForm
            v-model:pass-mark-percentage="passMarkPercentage"
            v-model:time-limit-minutes="timeLimitMinutes"
            v-model:max-attempts="maxAttempts"
            v-model:shuffle-questions="shuffleQuestions"
            v-model:show-correct-answers="showCorrectAnswers"
            :errors="errors"
        />
        <QuizPublishingForm v-model:published-at="publishedAt" :errors="errors" />
        <QuizQuestionsForm
            v-model:questions="questions"
            :errors="errors"
            :question-options="questionOptions"
        />
    </div>
</template>
