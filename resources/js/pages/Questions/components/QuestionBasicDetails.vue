<script setup lang="ts">
import type { Question } from '@/types';

defineProps<{ question: Question }>();
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">Question</h2>
        <p class="whitespace-pre-line">{{ question.question }}</p>
        <template v-if="question.explanation">
            <h3 class="text-sm font-medium">Explanation</h3>
            <p class="text-sm whitespace-pre-line">
                {{ question.explanation }}
            </p>
        </template>
    </section>
</template>
