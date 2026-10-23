<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import QuizForm from '@/pages/Quizzes/components/QuizForm.vue';
import type { QuizQuestionSelection } from '@/pages/Quizzes/components/QuizQuestionsForm.vue';
import { index as quizzesIndex, show, update } from '@/routes/quizzes';
import type { CourseOption, QuestionOption, Quiz } from '@/types';

const props = defineProps<{
    quiz: Quiz;
    courses: CourseOption[];
    questionOptions: QuestionOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Quizzes', href: quizzesIndex().url },
            { title: 'Quiz', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    course_id: props.quiz.course_id as number | null,
    lesson_id: props.quiz.lesson_id,
    title: props.quiz.title,
    description: props.quiz.description ?? '',
    pass_mark_percentage: props.quiz.pass_mark_percentage,
    time_limit_minutes: props.quiz.time_limit_minutes,
    max_attempts: props.quiz.max_attempts,
    shuffle_questions: props.quiz.shuffle_questions,
    show_correct_answers: props.quiz.show_correct_answers,
    published_at: props.quiz.published_at_iso?.slice(0, 16) ?? '',
    questions: (props.quiz.questions ?? []).map((question) => ({
        question_id: question.id,
        points: question.points ?? null,
    })) as QuizQuestionSelection[],
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
    })).put(update(props.quiz.id).url);
}
</script>

<template>
    <Head :title="`Edit ${quiz.title}`" />

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
                Save changes
            </Button>
            <Button as-child variant="outline">
                <Link :href="show(props.quiz.id).url">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
