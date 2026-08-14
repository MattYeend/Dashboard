<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as ticketStatusesStore } from '@/routes/ticket-statuses';
import TicketStatusForm from './components/TicketStatusForm.vue';
import type { TicketStatusFormData } from './components/TicketStatusForm.vue';

const form = useForm<TicketStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: TicketStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(ticketStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Ticket Status
            </h1>
            <TicketStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Ticket Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
