<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import TicketAssignmentForm from '@/pages/Tickets/components/TicketAssignmentForm.vue';
import TicketBasicDetailsForm from '@/pages/Tickets/components/TicketBasicDetailsForm.vue';
import TicketLabelsForm from '@/pages/Tickets/components/TicketLabelsForm.vue';
import TicketPriorityForm from '@/pages/Tickets/components/TicketPriorityForm.vue';
import TicketStatusForm from '@/pages/Tickets/components/TicketStatusForm.vue';
import { index as ticketsIndex } from '@/routes/tickets';
import type { Label, TicketPriority, TicketStatus, UserOption } from '@/types';

interface TicketFormData {
    title: string;
    description: string | null;
    ticket_status_id: number | null;
    ticket_priority_id: number | null;
    assigned_to: number | null;
    due_date: string | null;
    label_ids: number[];
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<TicketFormData>['errors']>;
    statuses: TicketStatus[];
    priorities: TicketPriority[];
    users: UserOption[];
    availableLabels: Label[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const ticketStatusId = defineModel<number | null>('ticketStatusId', {
    default: null,
});
const ticketPriorityId = defineModel<number | null>('ticketPriorityId', {
    default: null,
});
const assignedTo = defineModel<number | null>('assignedTo', {
    default: null,
});
const dueDate = defineModel<string | null>('dueDate', { default: null });
const labelIds = defineModel<number[]>('labelIds', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <TicketBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            :errors="errors"
        />

        <TicketStatusForm
            v-model:ticket-status-id="ticketStatusId"
            :statuses="statuses"
            :errors="errors"
        />

        <TicketPriorityForm
            v-model:ticket-priority-id="ticketPriorityId"
            :priorities="priorities"
            :errors="errors"
        />

        <TicketAssignmentForm
            v-model:assigned-to="assignedTo"
            v-model:due-date="dueDate"
            :users="users"
            :errors="errors"
        />

        <TicketLabelsForm
            v-model:label-ids="labelIds"
            :available-labels="availableLabels"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="ticketsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Ticket' : 'Create Ticket' }}
            </Button>
        </div>
    </form>
</template>
