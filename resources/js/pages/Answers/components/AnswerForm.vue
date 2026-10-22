<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { store, update } from '@/routes/answers';
import type { Answer, SelectOption } from '@/types';
import type { AnswerFormData } from '@/types/forms/answer';
import AnswerBasicDetailsForm from './AnswerBasicDetailsForm.vue';
import AnswerCorrectnessForm from './AnswerCorrectnessForm.vue';
import AnswerQuestionForm from './AnswerQuestionForm.vue';

const props = defineProps<{
    answer?: Answer | null;
    questionOptions?: SelectOption[];
    defaultQuestionId?: number | null;
    canViewCorrect: boolean;
}>();

const isEditing = computed(() => props.answer != null);

const form = useForm<AnswerFormData>({
    question_id: props.answer?.question_id ?? props.defaultQuestionId ?? null,
    answer: props.answer?.answer ?? '',
    is_correct: props.answer?.is_correct ?? false,
    feedback: props.answer?.feedback ?? null,
    position: props.answer?.position ?? null,
});

function submit(): void {
    if (props.answer) {
        form.put(update.url(props.answer.id));

        return;
    }

    form.post(store.url());
}
</script>

<template>
    <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
        <AnswerQuestionForm
            v-model:question-id="form.question_id"
            :question-options="questionOptions ?? []"
            :disabled="isEditing"
            :errors="form.errors"
        />

        <AnswerBasicDetailsForm
            v-model:answer="form.answer"
            v-model:position="form.position"
            :errors="form.errors"
        />

        <AnswerCorrectnessForm
            v-if="canViewCorrect"
            v-model:is-correct="form.is_correct"
            v-model:feedback="form.feedback"
            :errors="form.errors"
        />

        <div>
            <Button type="submit" :disabled="form.processing">
                {{ isEditing ? 'Save changes' : 'Create answer' }}
            </Button>
        </div>
    </form>
</template>
