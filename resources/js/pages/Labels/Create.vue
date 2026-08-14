<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as labelsStore } from '@/routes/labels';
import LabelForm from './components/LabelForm.vue';
import type { LabelFormData } from './components/LabelForm.vue';

const form = useForm<LabelFormData>({
    name: '',
    slug: null,
    background_colour: '#6b7280',
    text_colour: '#ffffff',
});

function onFormUpdate(updated: LabelFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
    })).post(labelsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Label
            </h1>
            <LabelForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Label"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
