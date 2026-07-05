<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import IndustryBasicDetailsForm from './IndustryBasicDetailsForm.vue';
import IndustryDescriptionForm from './IndustryDescriptionForm.vue';
import { index as industriesIndex } from '@/routes/industries';

export interface IndustryFormData {
    title: string;
    code: string | null;
    description: string | null;
}

interface Errors {
    title?: string;
    code?: string;
    description?: string;
}

defineProps<{
    form: IndustryFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: IndustryFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <IndustryBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <IndustryDescriptionForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="industriesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>