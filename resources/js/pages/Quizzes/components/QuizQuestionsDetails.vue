<script setup lang="ts">
import type { QuizQuestion } from '@/types';

defineProps<{
    questions: QuizQuestion[];
    canEdit: boolean;
}>();
</script>

<template>
    <section class="grid gap-4 border-b border-gray-500 pb-4">
        <h2 class="text-lg text-gray-300">
            Questions ({{ questions.length }})
        </h2>

        <ol class="grid gap-4">
            <li
                v-for="question in questions"
                :key="question.id"
                class="grid gap-2"
            >
                <p class="text-sm text-gray-300">
                    {{ question.position }}. {{ question.prompt }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ question.type_label }},
                    {{ question.effective_points }} point(s)
                </p>

                <ul class="grid gap-1 pl-4">
                    <li
                        v-for="answer in question.answers"
                        :key="answer.id"
                        class="text-sm text-gray-300"
                    >
                        {{ answer.answer }}
                        <span
                            v-if="canEdit && answer.is_correct"
                            class="text-xs text-gray-400"
                        >
                            (correct)
                        </span>
                    </li>
                </ul>

                <p
                    v-if="canEdit && question.explanation"
                    class="text-xs text-gray-400"
                >
                    Explanation: {{ question.explanation }}
                </p>
            </li>
        </ol>
    </section>
</template>
