<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { OrderStatusFormData } from './OrderStatusForm.vue';

interface Errors {
    background_colour?: string;
    text_colour?: string;
}

const props = defineProps<{
    form: OrderStatusFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: OrderStatusFormData): void;
}>();

function update<K extends keyof OrderStatusFormData>(
    field: K,
    value: OrderStatusFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="background_colour">Background Colour</Label>
            <div class="mt-1 flex items-center gap-3">
                <input
                    :value="form.background_colour"
                    type="color"
                    class="h-10 w-16 cursor-pointer rounded border"
                    @input="
                        update(
                            'background_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
                <Input
                    id="background_colour"
                    :model-value="form.background_colour"
                    type="text"
                    maxlength="7"
                    class="w-32 font-mono"
                    placeholder="#ffffff"
                    @update:model-value="
                        update('background_colour', $event as string)
                    "
                />
            </div>
            <InputError :message="errors.background_colour" />
        </div>
        <div>
            <Label for="text_colour">Text Colour</Label>
            <div class="mt-1 flex items-center gap-3">
                <input
                    :value="form.text_colour"
                    type="color"
                    class="h-10 w-16 cursor-pointer rounded border"
                    @input="
                        update(
                            'text_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
                <Input
                    id="text_colour"
                    :model-value="form.text_colour"
                    type="text"
                    maxlength="7"
                    class="w-32 font-mono"
                    placeholder="#000000"
                    @update:model-value="
                        update('text_colour', $event as string)
                    "
                />
            </div>
            <InputError :message="errors.text_colour" />
        </div>
        <div>
            <Label>Preview</Label>
            <div class="mt-1">
                <span
                    class="inline-block rounded px-3 py-1 text-sm font-medium"
                    :style="{
                        backgroundColor: form.background_colour,
                        color: form.text_colour,
                    }"
                >
                    {{ form.title || 'Status Label' }}
                </span>
            </div>
        </div>
    </div>
</template>