<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/questions';
import type { Answer } from '@/types';

defineProps<{ answer: Answer }>();
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">Question</h2>
        <p v-if="answer.question" class="text-sm">
            <Link :href="show.url(answer.question.id)" class="underline">
                #{{ answer.question.id }} {{ answer.question.question }}
            </Link>
        </p>
    </section>
</template>
