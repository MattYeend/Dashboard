<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { TaskStatus } from '@/types';
import TaskStatusBasicDetailsForm from './TaskStatusBasicDetailsForm.vue';
import TaskStatusColourForm from './TaskStatusColourForm.vue';
import TaskStatusMetaForm from './TaskStatusMetaForm.vue';

const props = defineProps<{
    action: string;
    method: 'post' | 'put';
    initial?: Partial<TaskStatus>;
}>();

const form = useForm({
    title: props.initial?.title ?? '',
    description: props.initial?.description ?? '',
    background_colour: props.initial?.background_colour ?? '#ffffff',
    text_colour: props.initial?.text_colour ?? '#000000',
    meta: props.initial?.meta ?? null,
});

function submit() {
    if (props.method === 'put') {
        form.put(props.action);
    } else {
        form.post(props.action);
    }
}
</script>

<template>
    <div class="space-y-6">
        <TaskStatusBasicDetailsForm :form="form" />
        <TaskStatusColourForm :form="form" />
        <TaskStatusMetaForm :form="form" />

        <div class="flex gap-2">
            <button type="button" @click="submit" :disabled="form.processing">
                Save
            </button>
            <Link :href="route('task-statuses.index')">Cancel</Link>
        </div>
    </div>
</template>
