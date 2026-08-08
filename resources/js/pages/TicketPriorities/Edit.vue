<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import type { TicketPriority } from '@/types';
import TicketPriorityForm from './components/TicketPriorityForm.vue';
import type { TicketPriorityFormData } from './components/TicketPriorityForm.vue';
import { update as ticketPrioritiesUpdate } from '@/routes/ticket-priorities';

const props = defineProps<{
    ticketPriority: TicketPriority;
}>();

const form = useForm<TicketPriorityFormData>({
    title: props.ticketPriority.title,
    level: props.ticketPriority.level,
    background_colour: props.ticketPriority.background_colour,
    text_colour: props.ticketPriority.text_colour,
});

function onFormUpdate(updated: TicketPriorityFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.put(ticketPrioritiesUpdate.url(props.ticketPriority.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Ticket Priority
            </h1>
            <TicketPriorityForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Ticket Priority"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
