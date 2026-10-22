<script setup lang="ts">
import type { Answer } from '@/types';

defineProps<{ answer: Answer }>();
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">Correctness</h2>
        <p class="text-sm">
            {{ answer.is_correct ? 'Correct answer' : 'Incorrect answer' }}
        </p>
        <template v-if="answer.feedback">
            <h3 class="text-sm font-medium">Feedback</h3>
            <p class="text-sm whitespace-pre-line">{{ answer.feedback }}</p>
        </template>
    </section>
</template>
