<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { store, update } from '@/routes/questions';
import type { Question, SelectOption } from '@/types';
import type { QuestionFormData } from '@/types/forms/question';
import QuestionBasicDetailsForm from './QuestionBasicDetailsForm.vue';
import QuestionLinkDetailsForm from './QuestionLinkDetailsForm.vue';
import QuestionTypeDetailsForm from './QuestionTypeDetailsForm.vue';

const props = defineProps<{
    question?: Question | null;
    typeOptions: SelectOption[];
    questionableTypes: SelectOption[];
}>();

const isEditing = computed(() => props.question != null);

const form = useForm<QuestionFormData>({
    questionable_type:
        props.question?.questionable_type ??
        props.questionableTypes[0]?.value ??
        '',
    questionable_id: props.question?.questionable_id ?? null,
    question: props.question?.question ?? '',
    type: props.question?.type ?? 'single_choice',
    explanation: props.question?.explanation ?? null,
    points: props.question?.points ?? 1,
    position: props.question?.position ?? null,
    is_required: props.question?.is_required ?? true,
});

function submit(): void {
    if (props.question) {
        form.put(update.url(props.question.id));

        return;
    }

    form.post(store.url());
}
</script>

<template>
    <form class="grid max-w-3xl gap-6" @submit.prevent="submit">
        <QuestionLinkDetailsForm
            v-model:questionable-type="form.questionable_type"
            v-model:questionable-id="form.questionable_id"
            :questionable-types="questionableTypes"
            :disabled="isEditing"
            :errors="form.errors"
        />

        <QuestionBasicDetailsForm
            v-model:question="form.question"
            v-model:explanation="form.explanation"
            :errors="form.errors"
        />

        <QuestionTypeDetailsForm
            v-model:type="form.type"
            v-model:points="form.points"
            v-model:position="form.position"
            v-model:is-required="form.is_required"
            :type-options="typeOptions"
            :errors="form.errors"
        />

        <div>
            <Button type="submit" :disabled="form.processing">
                {{ isEditing ? 'Save changes' : 'Create question' }}
            </Button>
        </div>
    </form>
</template>
