<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { create, show } from '@/routes/quizzes';
import type { Quiz } from '@/types';

defineProps<{
    courseId: number;
    quizzes: Quiz[];
    canEdit: boolean;
}>();
</script>

<template>
    <section class="grid gap-3 border-b border-gray-500 pb-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg text-gray-300">Quizzes</h2>
            <Button v-if="canEdit" as-child size="sm">
                <Link :href="create({ query: { course_id: courseId } }).url">
                    Add quiz
                </Link>
            </Button>
        </div>

        <p v-if="quizzes.length === 0" class="text-sm text-gray-400">
            No quizzes yet.
        </p>

        <ul v-else class="grid gap-2">
            <li
                v-for="quiz in quizzes"
                :key="quiz.id"
                class="flex items-center justify-between"
            >
                <Link :href="show(quiz.id).url" class="text-sm text-gray-300">
                    {{ quiz.title }}
                </Link>
                <span class="text-xs text-gray-400">
                    {{ quiz.questions_count ?? 0 }} questions, pass mark
                    {{ quiz.pass_mark_percentage }}%
                </span>
            </li>
        </ul>
    </section>
</template>
