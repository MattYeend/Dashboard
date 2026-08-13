<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { TicketStatus } from '@/types';

interface TicketStatusFormData {
    ticket_status_id: number | null;
}

interface Props {
    statuses: TicketStatus[];
    errors: Partial<InertiaFormProps<TicketStatusFormData>['errors']>;
}

defineProps<Props>();

const ticketStatusId = defineModel<number | null>('ticketStatusId', {
    default: null,
});
</script>

<template>
    <div>
        <Label for="ticket_status_id">Status</Label>
        <Select v-model="ticketStatusId">
            <SelectTrigger id="ticket_status_id" class="mt-1 w-full">
                <SelectValue placeholder="Select a status" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.title }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.ticket_status_id" />
    </div>
</template>
