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
import type { TicketPriority } from '@/types';

interface TicketPriorityFormData {
    ticket_priority_id: number | null;
}

interface Props {
    priorities: TicketPriority[];
    errors: Partial<InertiaFormProps<TicketPriorityFormData>['errors']>;
}

defineProps<Props>();

const ticketPriorityId = defineModel<number | null>('ticketPriorityId', {
    default: null,
});
</script>

<template>
    <div>
        <Label for="ticket_priority_id">Priority</Label>
        <Select v-model="ticketPriorityId">
            <SelectTrigger id="ticket_priority_id" class="mt-1 w-full">
                <SelectValue placeholder="Select a priority" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="priority in priorities"
                    :key="priority.id"
                    :value="priority.id"
                >
                    {{ priority.title }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.ticket_priority_id" />
    </div>
</template>
