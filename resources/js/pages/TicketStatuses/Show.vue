<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    edit as ticketStatusesEdit,
    destroy as ticketStatusesDestroy,
    index as ticketStatusIndex,
} from '@/routes/ticket-statuses';
import type { TicketStatus, PermissionsMeta } from '@/types';
import TicketStatusAuditDetails from './components/TicketStatusAuditDetails.vue';
import TicketStatusBasicDetails from './components/TicketStatusBasicDetails.vue';
import TicketStatusColourDetails from './components/TicketStatusColourDetails.vue';

interface Props {
    ticketStatus: TicketStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.ticketStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.ticketStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(ticketStatusesDestroy.url(props.ticketStatus.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ ticketStatus.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link
                        :href="ticketStatusIndex.url()"
                    >
                        Back
                    </Link>
                    </Button>
                    <Button as-child>
                        <Link
                            :href="ticketStatusesEdit.url(ticketStatus.id)"
                        >
                            Edit
                        </Link>
                    </Button>
                    <Button as-child variant="destructive" @click="requestDestroy">
                        Delete
                    </Button>
                </div>
            </div>

            <div class="space-y-6">
                <TicketStatusBasicDetails :ticket-status="ticketStatus" />
                <TicketStatusColourDetails :ticket-status="ticketStatus" />
                <TicketStatusAuditDetails :ticket-status="ticketStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete ticket status"
            description="This ticket status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
