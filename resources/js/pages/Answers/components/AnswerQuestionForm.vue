<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import type { SelectOption } from '@/types';

defineProps<{
    questionOptions: SelectOption[];
    disabled?: boolean;
    errors: Partial<Record<'question_id', string>>;
}>();

const questionId = defineModel<number | null>('questionId', { required: true });
</script>

<template>
    <div class="grid gap-2">
        <Label for="question_id">Question</Label>
        <select
            id="question_id"
            v-model.number="questionId"
            :disabled="disabled"
            class="w-full rounded-md border border-input px-3 py-2 text-sm"
            required
        >
            <option :value="null" disabled>Select a question</option>
            <option
                v-for="option in questionOptions"
                :key="option.value"
                :value="Number(option.value)"
            >
                {{ option.label }}
            </option>
        </select>
        <InputError :message="errors.question_id" />
    </div>
</template>
