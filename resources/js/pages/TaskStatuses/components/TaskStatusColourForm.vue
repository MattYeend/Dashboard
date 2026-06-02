<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

const props = defineProps<{ form: InertiaForm<any> }>();
const emit = defineEmits<{
    (e: 'update:form', value: InertiaForm<any>): void;
}>();

function update(field: string, value: string): void {
    emit('update:form', {
        ...props.form,
        [field]: value,
    } as InertiaForm<any>);
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium"
                >Background Colour</label
            >
            <div class="flex items-center gap-3">
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
                <input
                    :value="form.background_colour"
                    type="text"
                    maxlength="7"
                    class="w-32 rounded border px-3 py-2 font-mono text-sm"
                    placeholder="#ffffff"
                    @input="
                        update(
                            'background_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
            </div>
            <p
                v-if="form.errors.background_colour"
                class="mt-1 text-xs text-red-500"
            >
                {{ form.errors.background_colour }}
            </p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Text Colour</label>
            <div class="flex items-center gap-3">
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
                <input
                    :value="form.text_colour"
                    type="text"
                    maxlength="7"
                    class="w-32 rounded border px-3 py-2 font-mono text-sm"
                    placeholder="#000000"
                    @input="
                        update(
                            'text_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
            </div>
            <p v-if="form.errors.text_colour" class="mt-1 text-xs text-red-500">
                {{ form.errors.text_colour }}
            </p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Preview</label>
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
</template>
