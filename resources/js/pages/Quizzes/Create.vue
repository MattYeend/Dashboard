<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import QuizForm from '@/pages/Quizzes/components/QuizForm.vue';
import type { QuizQuestionSelection } from '@/pages/Quizzes/components/QuizQuestionsForm.vue';
import { index as quizzesIndex, store } from '@/routes/quizzes';
import type { CourseOption, QuestionOption } from '@/types';

const props = defineProps<{
    courses: CourseOption[];
    questionOptions: QuestionOption[];
    selectedCourseId: number | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Quizzes', href: quizzesIndex().url },
            { title: 'Create', href: '#' },
        ],
    },
});

const form = useForm({
    course_id: props.selectedCourseId,
    lesson_id: null as number | null,
    title: '',
    description: '',
    pass_mark_percentage: 70,
    time_limit_minutes: null as number | null,
    max_attempts: null as number | null,
    shuffle_questions: false,
    show_correct_answers: true,
    published_at: '',
    questions: [] as QuizQuestionSelection[],
});

/**
 * Submit the form, converting blank optional fields to null.
 */
function submit(): void {
    form.transform((data) => ({
        ...data,
        lesson_id: numberOrNull(data.lesson_id),
        description: nullIfBlank(data.description),
        time_limit_minutes: numberOrNull(data.time_limit_minutes),
        max_attempts: numberOrNull(data.max_attempts),
        published_at: nullIfBlank(data.published_at),
        questions: data.questions.map((selection) => ({
            question_id: selection.question_id,
            points: numberOrNull(selection.points),
        })),
    })).post(store().url);
}
</script>

<template>
    <Head title="Create quiz" />

    <form class="mx-auto grid max-w-3xl gap-6 p-4" @submit.prevent="submit">
        <QuizForm
            v-model:course-id="form.course_id"
            v-model:lesson-id="form.lesson_id"
            v-model:title="form.title"
            v-model:description="form.description"
            v-model:pass-mark-percentage="form.pass_mark_percentage"
            v-model:time-limit-minutes="form.time_limit_minutes"
            v-model:max-attempts="form.max_attempts"
            v-model:shuffle-questions="form.shuffle_questions"
            v-model:show-correct-answers="form.show_correct_answers"
            v-model:published-at="form.published_at"
            v-model:questions="form.questions"
            :errors="form.errors"
            :courses="courses"
            :question-options="questionOptions"
        />

        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing">
                Create quiz
            </Button>
            <Button as-child variant="outline">
                <Link :href="quizzesIndex().url">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
