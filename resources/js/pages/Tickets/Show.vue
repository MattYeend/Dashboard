<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import TicketAssignmentDetails from '@/pages/Tickets/components/TicketAssignmentDetails.vue';
import TicketAuditDetails from '@/pages/Tickets/components/TicketAuditDetails.vue';
import TicketBasicDetails from '@/pages/Tickets/components/TicketBasicDetails.vue';
import TicketComments from '@/pages/Tickets/components/TicketComments.vue';
import TicketLabels from '@/pages/Tickets/components/TicketLabels.vue';
import TicketPriorityDetails from '@/pages/Tickets/components/TicketPriorityDetails.vue';
import TicketStatusDetails from '@/pages/Tickets/components/TicketStatusDetails.vue';
import type { Ticket } from '@/types';
import {
    edit as ticketsEdit,
    destroy as ticketsDestroy,
    resolve as ticketsResolve,
    index as ticketsIndex,
} from '@/routes/tickets';

interface Props {
    ticket: Ticket;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);
const resolveProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(ticketsDestroy.url(props.ticket.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

function resolve(): void {
    resolveProcessing.value = true;

    router.post(
        ticketsResolve.url(props.ticket.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                resolveProcessing.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ ticket.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="ticketsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <button
                        v-if="!ticket.resolved_at"
                        type="button"
                        class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-500 disabled:opacity-50"
                        :disabled="resolveProcessing"
                        @click="resolve"
                    >
                        Mark as resolved
                    </button>
                    <Link
                        :href="ticketsEdit.url(props.ticket.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <TicketBasicDetails :ticket="ticket" />
                <TicketStatusDetails :ticket="ticket" />
                <TicketPriorityDetails :ticket="ticket" />
                <TicketAssignmentDetails :ticket="ticket" />
                <TicketLabels :ticket="ticket" />
                <TicketComments :ticket="ticket" />
                <TicketAuditDetails :ticket="ticket" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete ticket"
            description="This ticket will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
