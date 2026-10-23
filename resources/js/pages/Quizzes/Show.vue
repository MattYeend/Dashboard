<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import QuizAuditDetails from '@/pages/Quizzes/components/QuizAuditDetails.vue';
import QuizBasicDetails from '@/pages/Quizzes/components/QuizBasicDetails.vue';
import QuizQuestionsDetails from '@/pages/Quizzes/components/QuizQuestionsDetails.vue';
import QuizSettingsDetails from '@/pages/Quizzes/components/QuizSettingsDetails.vue';
import {
    destroy as quizzesDestroy,
    edit,
    index as quizzesIndex,
} from '@/routes/quizzes';
import type { Quiz } from '@/types';

const props = defineProps<{
    quiz: Quiz;
    canEdit: boolean;
}>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

/**
 * Open the delete confirmation dialog.
 */
function requestDestroy(): void {
    if (!props.quiz?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

/**
 * Move the quiz to trash, then close the confirmation dialog.
 */
function destroy(): void {
    if (!props.quiz?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(quizzesDestroy.url(props.quiz.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Quizzes', href: quizzesIndex().url },
            { title: 'Details', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="quiz.title" />

    <div class="mx-auto grid max-w-3xl gap-6 p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-xl text-gray-300">{{ quiz.title }}</h1>
            <div class="flex gap-2">
                <Button as-child variant="outline">
                    <Link :href="quizzesIndex().url">Back</Link>
                </Button>
                <Button v-if="canEdit" as-child>
                    <Link :href="edit(props.quiz.id).url">Edit</Link>
                </Button>
                <button
                    v-if="canEdit"
                    type="button"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                    @click="requestDestroy"
                >
                    Delete
                </button>
            </div>
        </div>

        <QuizBasicDetails :quiz="quiz" />
        <QuizSettingsDetails :quiz="quiz" />
        <QuizQuestionsDetails
            :questions="quiz.questions ?? []"
            :can-edit="canEdit"
        />
        <QuizAuditDetails v-if="canEdit" :quiz="quiz" />

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete quiz"
            description="This quiz will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
