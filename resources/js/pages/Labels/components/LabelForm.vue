<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import LabelBasicDetailsForm from './LabelBasicDetailsForm.vue';
import LabelColourForm from './LabelColourForm.vue';
import { index as labelsIndex } from '@/routes/labels';

export interface LabelFormData {
    name: string;
    slug: string | null;
    background_colour: string;
    text_colour: string;
}

interface Errors {
    name?: string;
    slug?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: LabelFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: LabelFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <LabelBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <LabelColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="labelsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
