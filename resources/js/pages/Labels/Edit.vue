<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as labelsUpdate } from '@/routes/labels';
import type { Label } from '@/types';
import LabelForm from './components/LabelForm.vue';
import type { LabelFormData } from './components/LabelForm.vue';

const props = defineProps<{
    label: Label;
}>();

const form = useForm<LabelFormData>({
    name: props.label.name,
    slug: props.label.slug,
    background_colour: props.label.background_colour,
    text_colour: props.label.text_colour,
});

function onFormUpdate(updated: LabelFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
    })).put(labelsUpdate.url(props.label.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Label
            </h1>
            <LabelForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Label"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
