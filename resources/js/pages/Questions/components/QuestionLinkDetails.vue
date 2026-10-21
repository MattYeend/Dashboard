<script setup lang="ts">
import type { Question } from '@/types';

defineProps<{ question: Question }>();
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">Belongs to</h2>
        <p class="text-sm">
            <span class="capitalize">{{ question.questionable_type }}</span
            >:
            {{ question.questionable?.label ?? `#${question.questionable_id}` }}
        </p>
    </section>
</template>
