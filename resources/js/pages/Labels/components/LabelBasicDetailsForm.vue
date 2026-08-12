<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label as FormLabel } from '@/components/ui/label';
import type { LabelFormData } from './LabelForm.vue';

interface Errors {
    name?: string;
    slug?: string;
}

const props = defineProps<{
    form: LabelFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: LabelFormData): void;
}>();

function update<K extends keyof LabelFormData>(
    field: K,
    value: LabelFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <FormLabel for="name">
                Name <span class="text-destructive">*</span>
            </FormLabel>
            <Input
                id="name"
                :model-value="form.name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter label name"
                @update:model-value="update('name', $event as string)"
            />
            <InputError :message="errors.name" />
        </div>
    </div>
</template>
