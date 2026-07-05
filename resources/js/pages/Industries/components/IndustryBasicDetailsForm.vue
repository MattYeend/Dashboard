<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { IndustryFormData } from './IndustryForm.vue';

interface Errors {
    title?: string;
    code?: string;
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
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                :model-value="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter industry title"
                @update:model-value="update('title', $event as string)"
            />
            <InputError :message="errors.title" />
        </div>
        <div>
            <Label for="code">SIC Code</Label>
            <Input
                id="code"
                :model-value="form.code ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter UK SIC 2007 code"
                @update:model-value="
                    update('code', ($event as string) || null)
                "
            />
            <InputError :message="errors.code" />
        </div>
    </div>
</template>