<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<'question' | 'explanation', string>>;
}>();

const question = defineModel<string>('question', { required: true });
const explanation = defineModel<string | null>('explanation', {
    required: true,
});
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-2">
            <Label for="question">Question</Label>
            <textarea
                id="question"
                v-model="question"
                rows="3"
                maxlength="2000"
                class="w-full rounded-md border border-input px-3 py-2 text-sm"
                required
            />
            <InputError :message="errors.question" />
        </div>

        <div class="grid gap-2">
            <Label for="explanation">Explanation (shown after answering)</Label>
            <textarea
                id="explanation"
                v-model="explanation"
                rows="3"
                maxlength="5000"
                class="w-full rounded-md border border-input px-3 py-2 text-sm"
            />
            <InputError :message="errors.explanation" />
        </div>
    </div>
</template>
