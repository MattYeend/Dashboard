<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toNullableNumber } from '@/lib/numbers';

defineProps<{
    errors: Partial<Record<string, string>>;
}>();

const passMarkPercentage = defineModel<number>('passMarkPercentage', {
    required: true,
});
const timeLimitMinutes = defineModel<number | null>('timeLimitMinutes', {
    required: true,
});
const maxAttempts = defineModel<number | null>('maxAttempts', {
    required: true,
});
const shuffleQuestions = defineModel<boolean>('shuffleQuestions', {
    required: true,
});
const showCorrectAnswers = defineModel<boolean>('showCorrectAnswers', {
    required: true,
});
</script>

<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-3">
        <div class="grid gap-2">
            <Label for="pass-mark">Pass mark (%)</Label>
            <Input
                id="pass-mark"
                :model-value="passMarkPercentage"
                type="number"
                min="1"
                max="100"
                required
                @update:model-value="passMarkPercentage = Number($event)"
            />
            <InputError :message="errors.pass_mark_percentage" />
        </div>

        <div class="grid gap-2">
            <Label for="time-limit">Time limit (minutes)</Label>
            <Input
                id="time-limit"
                :model-value="timeLimitMinutes ?? ''"
                type="number"
                min="1"
                max="600"
                @update:model-value="
                    timeLimitMinutes = toNullableNumber($event)
                "
            />
            <InputError :message="errors.time_limit_minutes" />
        </div>

        <div class="grid gap-2">
            <Label for="max-attempts">Maximum attempts</Label>
            <Input
                id="max-attempts"
                :model-value="maxAttempts ?? ''"
                type="number"
                min="1"
                max="50"
                @update:model-value="maxAttempts = toNullableNumber($event)"
            />
            <InputError :message="errors.max_attempts" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="shuffle"
                v-model="shuffleQuestions"
                type="checkbox"
                class="size-4"
            />
            <Label for="shuffle">Shuffle questions</Label>
        </div>

        <div class="flex items-center gap-2">
            <input
                id="show-answers"
                v-model="showCorrectAnswers"
                type="checkbox"
                class="size-4"
            />
            <Label for="show-answers">
                Show correct answers after submission
            </Label>
        </div>
    </div>
</template>
