<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { IndustryFormData } from './IndustryForm.vue';

interface Errors {
    description?: string;
}

const props = defineProps<{
    form: IndustryFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: IndustryFormData): void;
}>();

function update<K extends keyof IndustryFormData>(
    field: K,
    value: IndustryFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="form.description ?? ''"
                rows="3"
                class="mt-1 block w-full"
                placeholder="Enter industry description"
                @update:model-value="
                    update('description', ($event as string) || null)
                "
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>
