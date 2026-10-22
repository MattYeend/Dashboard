<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<'is_correct' | 'feedback', string>>;
}>();

const isCorrect = defineModel<boolean>('isCorrect', { required: true });
const feedback = defineModel<string | null>('feedback', { required: true });
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center gap-2">
            <input
                id="is_correct"
                v-model="isCorrect"
                type="checkbox"
                class="size-4"
            />
            <Label for="is_correct">Correct answer</Label>
            <InputError :message="errors.is_correct" />
        </div>

        <div class="grid gap-2">
            <Label for="feedback">Feedback (shown after answering)</Label>
            <textarea
                id="feedback"
                v-model="feedback"
                rows="2"
                maxlength="5000"
                class="w-full rounded-md border border-input px-3 py-2 text-sm"
            />
            <InputError :message="errors.feedback" />
        </div>
    </div>
</template>
