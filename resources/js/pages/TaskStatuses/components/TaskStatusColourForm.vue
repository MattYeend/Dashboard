<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'

defineProps<{ form: InertiaForm<any> }>()
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Background Colour</label>
                <div class="flex items-centre gap-3">
                    <input
                        v-model="form.background_colour"
                        type="color"
                        class="h-10 w-16 cursor-pointer rounded border"
                    />
                    <input
                        v-model="form.background_colour"
                        type="text"
                        maxlength="7"
                        class="border rounded px-3 py-2 w-32 font-mono text-sm"
                        placeholder="#ffffff"
                    />
                    </div>
                    <p v-if="form.errors.background_colour" class="text-red-500 text-xs mt-1">
                        {{ form.errors.background_colour }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Text Colour</label>
                    <div class="flex items-centre gap-3">
                        <input
                            v-model="form.text_colour"
                            type="color"
                            class="h-10 w-16 cursor-pointer rounded border"
                        />
                        <input
                            v-model="form.text_colour"
                            type="text"
                            maxlength="7"
                            class="border rounded px-3 py-2 w-32 font-mono text-sm"
                            placeholder="#000000"
                        />
                    </div>
                    <p v-if="form.errors.text_colour" class="text-red-500 text-xs mt-1">
                        {{ form.errors.text_colour }}
                    </p>
                </div>
            <div>
            <label class="block text-sm font-medium mb-1">Preview</label>
            <span
                class="inline-block px-3 py-1 rounded text-sm font-medium"
                :style="{ backgroundColor: form.background_colour, color: form.text_colour }"
            >
                {{ form.title || 'Status Label' }}
            </span>
        </div>
    </div>
</template>