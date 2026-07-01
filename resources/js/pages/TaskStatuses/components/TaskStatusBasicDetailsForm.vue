<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { TaskStatusFormData } from './TaskStatusForm.vue';

interface Errors {
    title?: string;
    description?: string;
}

const props = defineProps<{
    form: TaskStatusFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: TaskStatusFormData): void;
}>();

function update<K extends keyof TaskStatusFormData>(
    field: K,
    value: TaskStatusFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                :model-value="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter status title"
                @update:model-value="update('title', $event as string)"
            />
            <InputError :message="errors.title" />
        </div>
        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="form.description ?? ''"
                rows="3"
                class="mt-1 block w-full"
                placeholder="Enter status description"
                @update:model-value="
                    update('description', ($event as string) || null)
                "
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>
