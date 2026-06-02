<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'

defineProps<{ form: InertiaForm<any> }>()
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
        <textarea
            :value="form.meta ? JSON.stringify(form.meta, null, 2) : ''"
            @input="(e) => {
                try { form.meta = JSON.parse((e.target as HTMLTextAreaElement).value) }
                catch { form.meta = null }
            }"
            rows="4"
            class="w-full border rounded px-3 py-2 font-mono text-sm"
            placeholder='{ "key": "value" }'
        />
        <p v-if="form.errors.meta" class="text-red-500 text-xs mt-1">
            {{ form.errors.meta }}
        </p>
    </div>
</template>