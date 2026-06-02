<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

defineProps<{ form: InertiaForm<any> }>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium">Title</label>
            <input
                v-model="form.title"
                type="text"
                class="w-full rounded border px-3 py-2"
            />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">
                {{ form.errors.title }}
            </p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Description</label>
            <textarea
                v-model="form.description"
                rows="3"
                class="w-full rounded border px-3 py-2"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                {{ form.errors.description }}
            </p>
        </div>
    </div>
</template>
