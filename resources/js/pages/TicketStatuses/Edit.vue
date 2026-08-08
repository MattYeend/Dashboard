<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import type { TicketStatus } from '@/types';
import TicketStatusForm from './components/TicketStatusForm.vue';
import type { TicketStatusFormData } from './components/TicketStatusForm.vue';
import { update as ticketStatusesUpdate } from '@/routes/ticket-statuses';

const props = defineProps<{
    ticketStatus: TicketStatus;
}>();

const form = useForm<TicketStatusFormData>({
    title: props.ticketStatus.title,
    description: props.ticketStatus.description,
    background_colour: props.ticketStatus.background_colour,
    text_colour: props.ticketStatus.text_colour,
});

function onFormUpdate(updated: TicketStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).put(ticketStatusesUpdate.url(props.ticketStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Ticket Status
            </h1>
            <TicketStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Ticket Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
