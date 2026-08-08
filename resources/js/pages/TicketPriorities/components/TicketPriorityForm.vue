<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import TicketPriorityBasicDetailsForm from './TicketPriorityBasicDetailsForm.vue';
import TicketPriorityColourForm from './TicketPriorityColourForm.vue';
import { index as ticketPrioritiesIndex } from '@/routes/ticket-priorities';

export interface TicketPriorityFormData {
    title: string;
    level: number;
    background_colour: string;
    text_colour: string;
}

interface Errors {
    title?: string;
    level?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: TicketPriorityFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: TicketPriorityFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <TicketPriorityBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <TicketPriorityColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="ticketPrioritiesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
