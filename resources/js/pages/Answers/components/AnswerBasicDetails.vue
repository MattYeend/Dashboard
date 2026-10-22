<script setup lang="ts">
import type { Answer } from '@/types';

defineProps<{ answer: Answer }>();
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">Answer</h2>
        <p class="whitespace-pre-line">{{ answer.answer }}</p>
        <p class="text-sm">Position: {{ answer.position }}</p>
    </section>
</template>
