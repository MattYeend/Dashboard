<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { parseNullableNumber } from '@/lib/numbers';
import type { QuestionTypeValue, SelectOption } from '@/types';

defineProps<{
    typeOptions: SelectOption[];
    errors: Partial<
        Record<'type' | 'points' | 'position' | 'is_required', string>
    >;
}>();

const type = defineModel<QuestionTypeValue>('type', { required: true });
const points = defineModel<number>('points', { required: true });
const position = defineModel<number | null>('position', { required: true });
const isRequired = defineModel<boolean>('isRequired', { required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="type">Type</Label>
            <select
                id="type"
                v-model="type"
                class="w-full rounded-md border border-input px-3 py-2 text-sm"
                required
            >
                <option
                    v-for="option in typeOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.type" />
        </div>

        <div class="grid gap-2">
            <Label for="points">Points</Label>
            <Input
                id="points"
                v-model.number="points"
                type="number"
                min="0"
                max="1000"
                required
            />
            <InputError :message="errors.points" />
        </div>

        <div class="grid gap-2">
            <Label for="position">Position (blank for next)</Label>
            <Input
                id="position"
                :model-value="position ?? ''"
                type="number"
                min="0"
                max="65535"
                @update:model-value="position = parseNullableNumber($event)"
            />
            <InputError :message="errors.position" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_required"
                v-model="isRequired"
                type="checkbox"
                class="size-4"
            />
            <Label for="is_required">Required</Label>
            <InputError :message="errors.is_required" />
        </div>
    </div>
</template>
