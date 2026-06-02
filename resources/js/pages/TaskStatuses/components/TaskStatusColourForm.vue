<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

defineProps<{ form: InertiaForm<any> }>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium"
                >Background Colour</label
            >
            <div class="items-centre flex gap-3">
                <input
                    v-model="form.background_colour"
                    type="color"
                    class="h-10 w-16 cursor-pointer rounded border"
                />
                <input
                    v-model="form.background_colour"
                    type="text"
                    maxlength="7"
                    class="w-32 rounded border px-3 py-2 font-mono text-sm"
                    placeholder="#ffffff"
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
            <div class="items-centre flex gap-3">
                <input
                    v-model="form.text_colour"
                    type="color"
                    class="h-10 w-16 cursor-pointer rounded border"
                />
                <input
                    v-model="form.text_colour"
                    type="text"
                    maxlength="7"
                    class="w-32 rounded border px-3 py-2 font-mono text-sm"
                    placeholder="#000000"
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
