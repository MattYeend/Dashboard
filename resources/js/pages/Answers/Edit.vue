<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Answer, AnswerPermissionsMeta } from '@/types';
import AnswerForm from './components/AnswerForm.vue';

defineProps<{
    answer: Answer;
    permissions_meta: AnswerPermissionsMeta;
}>();
</script>

<template>
    <Head title="Edit answer" />

    <AppLayout>
        <div class="grid gap-6 p-4">
            <h1 class="text-2xl font-semibold">Edit answer</h1>
            <AnswerForm
                :answer="answer"
                :can-view-correct="permissions_meta.can_view_correct"
            />
        </div>
    </AppLayout>
</template>
