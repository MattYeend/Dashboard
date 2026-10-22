<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SelectOption } from '@/types';
import AnswerForm from './components/AnswerForm.vue';

defineProps<{
    question_options: SelectOption[];
    default_question_id: number | null;
    permissions_meta: { can_view_correct: boolean };
}>();
</script>

<template>
    <Head title="Create answer" />

    <AppLayout>
        <div class="grid gap-6 p-4">
            <h1 class="text-2xl font-semibold">Create answer</h1>
            <AnswerForm
                :question-options="question_options"
                :default-question-id="default_question_id"
                :can-view-correct="permissions_meta.can_view_correct"
            />
        </div>
    </AppLayout>
</template>
