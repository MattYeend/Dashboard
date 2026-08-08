<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { TicketPriority, PermissionsMeta } from '@/types';
import TicketPriorityAuditDetails from './components/TicketPriorityAuditDetails.vue';
import TicketPriorityBasicDetails from './components/TicketPriorityBasicDetails.vue';
import TicketPriorityColourDetails from './components/TicketPriorityColourDetails.vue';
import {
    edit as ticketPrioritiesEdit,
    destroy as ticketPrioritiesDestroy,
    index as ticketPrioritiesIndex,
} from '@/routes/ticket-priorities';

interface Props {
    ticketPriority: TicketPriority;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.ticketPriority?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.ticketPriority?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(ticketPrioritiesDestroy.url(props.ticketPriority.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ ticketPriority.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="ticketPrioritiesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="ticketPrioritiesEdit.url(ticketPriority.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="permissions_meta.can_create"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <TicketPriorityBasicDetails :ticket-priority="ticketPriority" />
                <TicketPriorityColourDetails
                    :ticket-priority="ticketPriority"
                />
                <TicketPriorityAuditDetails :ticket-priority="ticketPriority" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete ticket priority"
            description="This ticket priority will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
