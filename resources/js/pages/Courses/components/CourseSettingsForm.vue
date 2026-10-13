<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DifficultyOption, UserOption } from '@/types';
 
defineProps<{
    errors: Partial<Record<string, string>>;
    difficulties: DifficultyOption[];
    instructors: UserOption[];
}>();
 
const difficulty = defineModel<string>('difficulty', { required: true });
const estimatedDurationMinutes = defineModel<number | null>('estimatedDurationMinutes', { required: true });
const instructorId = defineModel<number | null>('instructorId', { required: true });

/**
 * Bridge the nullable model to the Input component, which does not accept null.
 */
const durationInput = computed<string | number>({
    get: () => estimatedDurationMinutes.value ?? '',
    set: (value) => {
        estimatedDurationMinutes.value = value === '' ? null : Number(value);
    },
});
</script>
 
<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-3">
        <div class="grid gap-2">
            <Label for="difficulty">Difficulty</Label>
            <Select v-model="difficulty">
                <SelectTrigger id="difficulty">
                    <SelectValue placeholder="Select difficulty" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="option in difficulties" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.difficulty" />
        </div>
 
        <div class="grid gap-2">
            <Label for="estimated-duration">Estimated duration (minutes)</Label>
            <Input id="estimated-duration" v-model="durationInput" type="number" min="1" max="10080" />
            <InputError :message="errors.estimated_duration_minutes" />
        </div>
 
        <div class="grid gap-2">
            <Label for="instructor">Instructor</Label>
            <Select v-model="instructorId">
                <SelectTrigger id="instructor">
                    <SelectValue placeholder="Select instructor" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="user in instructors" :key="user.id" :value="user.id">
                        {{ user.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.instructor_id" />
        </div>
    </div>
</template>