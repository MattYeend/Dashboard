<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<'answer' | 'position', string>>;
}>();

const answer = defineModel<string>('answer', { required: true });
const position = defineModel<number | null>('position', { required: true });

const positionInput = computed<string | number>({
    get: () => position.value ?? '',
    set: (value) => {
        position.value = value === '' ? null : Number(value);
    },
});
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-2">
            <Label for="answer">Answer</Label>
            <textarea
                id="answer"
                v-model="answer"
                rows="2"
                maxlength="2000"
                class="w-full rounded-md border border-input px-3 py-2 text-sm"
                required
            />
            <InputError :message="errors.answer" />
        </div>

        <div class="grid gap-2 sm:max-w-48">
            <Label for="position">Position (blank for next)</Label>
            <Input
                id="position"
                v-model="positionInput"
                type="number"
                min="0"
                max="65535"
            />
            <InputError :message="errors.position" />
        </div>
    </div>
</template>
