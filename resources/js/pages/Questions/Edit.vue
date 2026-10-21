<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Question, SelectOption } from '@/types';
import QuestionForm from './components/QuestionForm.vue';

defineProps<{
    question: Question;
    typeOptions: SelectOption[];
    questionableTypes: SelectOption[];
}>();
</script>

<template>
    <Head title="Edit question" />

    <AppLayout>
        <div class="grid gap-6 p-4">
            <h1 class="text-2xl font-semibold">Edit question</h1>
            <QuestionForm
                :question="question"
                :type-options="typeOptions"
                :questionable-types="questionableTypes"
            />
        </div>
    </AppLayout>
</template>
