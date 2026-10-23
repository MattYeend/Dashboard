<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    destroy as quizzesDestroy,
    edit,
    index as quizzesIndex,
} from '@/routes/quizzes';
import type { Quiz } from '@/types';
import QuizAuditDetails from './components/QuizAuditDetails.vue';
import QuizBasicDetails from './components/QuizBasicDetails.vue';
import QuizQuestionsDetails from './components/QuizQuestionsDetails.vue';
import QuizSettingsDetails from './components/QuizSettingsDetails.vue';

interface Props {
    quiz: Quiz;
    canEdit: boolean;
}

const props = defineProps<Props>();

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
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ quiz.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
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

            <div class="space-y-6">
                <QuizBasicDetails :quiz="quiz" />
                <QuizSettingsDetails :quiz="quiz" />
                <QuizQuestionsDetails
                    :questions="quiz.questions ?? []"
                    :can-edit="canEdit"
                />
                <QuizAuditDetails v-if="canEdit" :quiz="quiz" />
            </div>
        </div>

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
