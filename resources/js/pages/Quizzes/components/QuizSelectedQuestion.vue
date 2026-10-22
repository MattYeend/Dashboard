<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toNullableNumber } from '@/lib/numbers';
import type { QuestionOption } from '@/types';

defineProps<{
    question: QuestionOption | undefined;
    index: number;
    total: number;
    error?: string;
}>();

const emit = defineEmits<{
    remove: [];
    moveUp: [];
    moveDown: [];
}>();

const points = defineModel<number | null>('points', { required: true });
</script>

<template>
    <li class="grid gap-2 border-b border-gray-500 pb-3">
        <p class="text-sm text-gray-300">
            {{ index + 1 }}. {{ question?.prompt ?? 'Question unavailable' }}
        </p>
        <p v-if="question" class="text-xs text-gray-400">
            {{ question.type_label }}
        </p>

        <div class="grid gap-2 sm:grid-cols-3 sm:items-end">
            <div class="grid gap-2">
                <Label :for="`points-${index}`">
                    Points (default {{ question?.points ?? 1 }})
                </Label>
                <Input
                    :id="`points-${index}`"
                    :model-value="points ?? ''"
                    type="number"
                    min="1"
                    max="100"
                    @update:model-value="points = toNullableNumber($event)"
                />
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="index === 0"
                    @click="emit('moveUp')"
                >
                    Up
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="index === total - 1"
                    @click="emit('moveDown')"
                >
                    Down
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="emit('remove')"
                >
                    Remove
                </Button>
            </div>
        </div>

        <InputError :message="error" />
    </li>
</template>
