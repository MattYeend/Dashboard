<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { parseNullableNumber } from '@/lib/numbers';
import type { SelectOption } from '@/types';

defineProps<{
    questionableTypes: SelectOption[];
    disabled?: boolean;
    errors: Partial<Record<'questionable_type' | 'questionable_id', string>>;
}>();

const questionableType = defineModel<string>('questionableType', {
    required: true,
});
const questionableId = defineModel<number | null>('questionableId', {
    required: true,
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="questionable_type">Belongs to</Label>
            <select
                id="questionable_type"
                v-model="questionableType"
                :disabled="disabled"
                class="w-full rounded-md border border-input px-3 py-2 text-sm"
                required
            >
                <option
                    v-for="option in questionableTypes"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.questionable_type" />
        </div>

        <div class="grid gap-2">
            <Label for="questionable_id">Item ID</Label>
            <Input
                id="questionable_id"
                :model-value="questionableId ?? ''"
                type="number"
                min="1"
                :disabled="disabled"
                required
                @update:model-value="
                    questionableId = parseNullableNumber($event)
                "
            />
            <InputError :message="errors.questionable_id" />
        </div>
    </div>
</template>
