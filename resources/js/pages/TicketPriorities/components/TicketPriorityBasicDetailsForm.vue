<script setup lang="ts">
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
import type { TicketPriorityFormData } from './TicketPriorityForm.vue';

interface Errors {
    title?: string;
    level?: string;
}

const props = defineProps<{
    form: TicketPriorityFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: TicketPriorityFormData): void;
}>();

const levelOptions = [
    { value: '1', label: 'Low' },
    { value: '2', label: 'Medium' },
    { value: '3', label: 'High' },
    { value: '4', label: 'Critical' },
];

function update<K extends keyof TicketPriorityFormData>(
    field: K,
    value: TicketPriorityFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title">
                Title <span class="text-destructive">*</span>
            </Label>
            <Input
                id="title"
                :model-value="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter priority title"
                @update:model-value="update('title', $event as string)"
            />
            <InputError :message="errors.title" />
        </div>
        <div>
            <Label for="level">Level</Label>
            <Select
                :model-value="String(form.level)"
                @update:model-value="update('level', Number($event))"
            >
                <SelectTrigger id="level" class="mt-1 w-full">
                    <SelectValue placeholder="Select a level" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in levelOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.level" />
        </div>
    </div>
</template>
