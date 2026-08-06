<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as dealStatusesIndex } from '@/routes/deal-statuses';
import DealStatusBasicDetailsForm from './DealStatusBasicDetailsForm.vue';
import DealStatusColourForm from './DealStatusColourForm.vue';

export interface DealStatusFormData {
    title: string;
    description: string | null;
    background_colour: string;
    text_colour: string;
}

interface Errors {
    title?: string;
    description?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: DealStatusFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: DealStatusFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <DealStatusBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <DealStatusColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="dealStatusesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
