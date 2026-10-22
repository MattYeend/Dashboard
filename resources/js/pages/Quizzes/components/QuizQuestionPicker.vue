<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { QuestionOption } from '@/types';

const props = defineProps<{
    options: QuestionOption[];
    selectedIds: number[];
    disabled: boolean;
}>();

const emit = defineEmits<{
    add: [questionId: number];
}>();

const search = ref('');

const available = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.options
        .filter((option) => !props.selectedIds.includes(option.id))
        .filter(
            (option) =>
                term === '' || option.prompt.toLowerCase().includes(term),
        )
        .slice(0, 20);
});
</script>

<template>
    <div class="grid gap-2 border-b border-gray-500 pb-4">
        <Label for="question-search">Add a question</Label>
        <Input
            id="question-search"
            v-model="search"
            placeholder="Search questions"
            :disabled="disabled"
        />

        <ul class="grid gap-1">
            <li
                v-for="option in available"
                :key="option.id"
                class="flex items-center justify-between gap-2"
            >
                <span class="text-sm text-gray-300">
                    {{ option.prompt }}
                    <span class="text-xs text-gray-400">
                        ({{ option.type_label }}, {{ option.points }} point(s))
                    </span>
                </span>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="disabled"
                    @click="emit('add', option.id)"
                >
                    Add
                </Button>
            </li>
        </ul>

        <p v-if="available.length === 0" class="text-xs text-gray-400">
            No matching questions available.
        </p>
    </div>
</template>
