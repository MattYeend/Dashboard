<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'

defineProps<{ form: InertiaForm<any> }>()
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input
                v-model="form.title"
                type="text"
                class="w-full border rounded px-3 py-2"
            />
            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">
                {{ form.errors.title }}
            </p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea
                v-model="form.description"
                rows="3"
                class="w-full border rounded px-3 py-2"
            />
            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">
                {{ form.errors.description }}
            </p>
        </div>
    </div>
</template>