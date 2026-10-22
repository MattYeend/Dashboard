<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { create, show } from '@/routes/answers';
import type { Answer } from '@/types';

defineProps<{
    questionId: number;
    answers: Answer[];
    canCreate: boolean;
    canViewCorrect: boolean;
}>();
</script>

<template>
    <section class="grid gap-2">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium">Answers</h2>
            <Button v-if="canCreate" as-child size="sm">
                <Link
                    :href="create.url({ query: { question_id: questionId } })"
                >
                    Add answer
                </Link>
            </Button>
        </div>

        <ol v-if="answers.length > 0" class="grid gap-1 text-sm">
            <li
                v-for="answer in answers"
                :key="answer.id"
                class="flex items-center gap-2"
            >
                <Link :href="show.url(answer.id)" class="underline">
                    {{ answer.answer }}
                </Link>
                <span
                    v-if="canViewCorrect && answer.is_correct"
                    class="font-medium"
                >
                    (correct)
                </span>
            </li>
        </ol>
        <p v-else class="text-sm">No answers yet.</p>
    </section>
</template>
