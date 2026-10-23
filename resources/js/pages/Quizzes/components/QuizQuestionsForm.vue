<script lang="ts">
export interface QuizQuestionSelection {
    question_id: number;
    points: number | null;
}
</script>

<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import QuizQuestionPicker from '@/pages/Quizzes/components/QuizQuestionPicker.vue';
import QuizSelectedQuestion from '@/pages/Quizzes/components/QuizSelectedQuestion.vue';
import type { QuestionOption } from '@/types';

const props = defineProps<{
    errors: Partial<Record<string, string>>;
    questionOptions: QuestionOption[];
}>();

const questions = defineModel<QuizQuestionSelection[]>('questions', {
    required: true,
});

const MAX_QUESTIONS = 100;

const selectedIds = computed(() =>
    questions.value.map((selection) => selection.question_id),
);

/**
 * Find the option details for a selected question.
 */
function optionFor(id: number): QuestionOption | undefined {
    return props.questionOptions.find((option) => option.id === id);
}

/**
 * Add a question to the end of the quiz.
 */
function addQuestion(questionId: number): void {
    if (
        questions.value.length >= MAX_QUESTIONS ||
        selectedIds.value.includes(questionId)
    ) {
        return;
    }

    questions.value = [
        ...questions.value,
        { question_id: questionId, points: null },
    ];
}

/**
 * Remove a question from the quiz.
 */
function removeQuestion(index: number): void {
    questions.value = questions.value.filter(
        (_, position) => position !== index,
    );
}

/**
 * Swap a question with its neighbour.
 */
function move(index: number, direction: -1 | 1): void {
    const target = index + direction;

    if (target < 0 || target >= questions.value.length) {
        return;
    }

    const next = [...questions.value];
    [next[index], next[target]] = [next[target], next[index]];
    questions.value = next;
}
</script>

<template>
    <section class="grid gap-4">
        <h2 class="text-lg text-gray-300">
            Questions ({{ questions.length }})
        </h2>
        <InputError :message="errors.questions" />

        <QuizQuestionPicker
            :options="questionOptions"
            :selected-ids="selectedIds"
            :disabled="questions.length >= MAX_QUESTIONS"
            @add="addQuestion"
        />

        <p v-if="questions.length === 0" class="text-sm text-gray-400">
            No questions chosen yet.
        </p>

        <ol v-else class="grid gap-3">
            <QuizSelectedQuestion
                v-for="(selection, index) in questions"
                :key="selection.question_id"
                v-model:points="selection.points"
                :question="optionFor(selection.question_id)"
                :index="index"
                :total="questions.length"
                :error="
                    errors[`questions.${index}.question_id`] ??
                    errors[`questions.${index}.points`]
                "
                @remove="removeQuestion(index)"
                @move-up="move(index, -1)"
                @move-down="move(index, 1)"
            />
        </ol>
    </section>
</template>
