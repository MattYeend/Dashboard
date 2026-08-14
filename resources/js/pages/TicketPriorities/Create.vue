<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as ticketPrioritiesStore } from '@/routes/ticket-priorities';
import TicketPriorityForm from './components/TicketPriorityForm.vue';
import type { TicketPriorityFormData } from './components/TicketPriorityForm.vue';

const form = useForm<TicketPriorityFormData>({
    title: '',
    level: 1,
    background_colour: '#6b7280',
    text_colour: '#ffffff',
});

function onFormUpdate(updated: TicketPriorityFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.post(ticketPrioritiesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Ticket Priority
            </h1>
            <TicketPriorityForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Ticket Priority"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
