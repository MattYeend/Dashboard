<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PlanFormData } from './PlanForm.vue';

interface Errors {
    name?: string;
    slug?: string;
}

const props = defineProps<{
    form: PlanFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: PlanFormData): void;
}>();

function update<K extends keyof PlanFormData>(
    field: K,
    value: PlanFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="name"
                >Name <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                :model-value="form.name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter plan name"
                @update:model-value="update('name', $event as string)"
            />
            <InputError :message="errors.name" />
        </div>
        <div>
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                :model-value="form.slug ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Leave blank to generate automatically"
                @update:model-value="update('slug', ($event as string) || null)"
            />
            <InputError :message="errors.slug" />
        </div>
    </div>
</template>
