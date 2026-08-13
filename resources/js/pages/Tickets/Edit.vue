<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import TicketForm from '@/pages/Tickets/components/TicketForm.vue';
import type {
    Label,
    Ticket,
    TicketPriority,
    TicketStatus,
    UserOption,
} from '@/types';
import { update as ticketsUpdate } from '@/routes/tickets';

interface Props {
    ticket: Ticket;
    ticket_statuses: TicketStatus[];
    ticket_priorities: TicketPriority[];
    users: UserOption[];
    labels: Label[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.ticket.title,
    description: props.ticket.description ?? '',
    ticket_status_id: props.ticket.status?.id ?? null,
    ticket_priority_id: props.ticket.priority?.id ?? null,
    assigned_to: props.ticket.assignee?.id ?? null,
    due_date: props.ticket.due_date,
    label_ids: props.ticket.labels?.map((label) => label.id) ?? [],
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        ticket_status_id: numberOrNull(data.ticket_status_id),
        ticket_priority_id: numberOrNull(data.ticket_priority_id),
        assigned_to: numberOrNull(data.assigned_to),
    })).put(ticketsUpdate.url(props.ticket.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Ticket
            </h1>

            <TicketForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:ticket-status-id="form.ticket_status_id"
                v-model:ticket-priority-id="form.ticket_priority_id"
                v-model:assigned-to="form.assigned_to"
                v-model:due-date="form.due_date"
                v-model:label-ids="form.label_ids"
                :statuses="ticket_statuses"
                :priorities="ticket_priorities"
                :users="users"
                :available-labels="labels"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
