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
            <label class="mb-1 block text-sm font-medium">Title</label>
            <input
                :value="form.title"
                type="text"
                class="w-full rounded border px-3 py-2"
                @input="
                    update('title', ($event.target as HTMLInputElement).value)
                "
            />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">
                {{ form.errors.title }}
            </p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Description</label>
            <textarea
                :value="form.description"
                rows="3"
                class="w-full rounded border px-3 py-2"
                @input="
                    update(
                        'description',
                        ($event.target as HTMLTextAreaElement).value,
                    )
                "
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                {{ form.errors.description }}
            </p>
        </div>
    </div>
</template>
