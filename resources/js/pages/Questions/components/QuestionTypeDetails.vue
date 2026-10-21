<script setup lang="ts">
import type { Question } from '@/types';

defineProps<{ question: Question }>();
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">Settings</h2>
        <dl class="grid gap-1 text-sm sm:grid-cols-2">
            <dt class="font-medium">Type</dt>
            <dd>{{ question.type_label }}</dd>
            <dt class="font-medium">Points</dt>
            <dd>{{ question.points }}</dd>
            <dt class="font-medium">Position</dt>
            <dd>{{ question.position }}</dd>
            <dt class="font-medium">Required</dt>
            <dd>{{ question.is_required ? 'Yes' : 'No' }}</dd>
        </dl>
    </section>
</template>
