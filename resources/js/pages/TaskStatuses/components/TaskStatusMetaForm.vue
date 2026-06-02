<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

const props = defineProps<{ form: InertiaForm<any> }>();

function updateMeta(value: string): void {
    try {
        props.form.put('meta', JSON.parse(value));
    } catch {
        props.form.put('meta', null);
    }
}
</script>

<template>
    <div>
        <label class="mb-1 block text-sm font-medium">Meta (JSON)</label>
        <textarea
            :value="form.meta ? JSON.stringify(form.meta, null, 2) : ''"
            rows="4"
            class="w-full rounded border px-3 py-2 font-mono text-sm"
            placeholder='{ "key": "value" }'
            @input="updateMeta(($event.target as HTMLTextAreaElement).value)"
        />
        <p v-if="form.errors.meta" class="mt-1 text-xs text-red-500">
            {{ form.errors.meta }}
        </p>
    </div>
</template>
