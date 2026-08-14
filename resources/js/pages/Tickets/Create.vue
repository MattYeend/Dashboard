<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import TicketForm from '@/pages/Tickets/components/TicketForm.vue';
import { store as ticketsStore } from '@/routes/tickets';
import type { Label, TicketPriority, TicketStatus, UserOption } from '@/types';

interface Props {
    ticket_statuses: TicketStatus[];
    ticket_priorities: TicketPriority[];
    users: UserOption[];
    labels: Label[];
}

defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    ticket_status_id: null as number | null,
    ticket_priority_id: null as number | null,
    assigned_to: null as number | null,
    due_date: null as string | null,
    label_ids: [] as number[],
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        ticket_status_id: numberOrNull(data.ticket_status_id),
        ticket_priority_id: numberOrNull(data.ticket_priority_id),
        assigned_to: numberOrNull(data.assigned_to),
    })).post(ticketsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Ticket
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
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
