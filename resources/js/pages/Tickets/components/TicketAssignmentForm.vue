<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { UserOption } from '@/types';

interface TicketAssignmentFormData {
    assigned_to: number | null;
    due_date: string | null;
}

interface Props {
    users: UserOption[];
    errors: Partial<InertiaFormProps<TicketAssignmentFormData>['errors']>;
}

defineProps<Props>();

const assignedTo = defineModel<number | null>('assignedTo', {
    default: null,
});
const dueDate = defineModel<string | null>('dueDate', { default: null });
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <Label for="assigned_to">Assignee</Label>
            <Select v-model="assignedTo">
                <SelectTrigger id="assigned_to" class="mt-1 w-full">
                    <SelectValue placeholder="Unassigned" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.assigned_to" />
        </div>

        <div>
            <Label for="due_date">Due date</Label>
            <Input
                id="due_date"
                :model-value="dueDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="dueDate = ($event as string) || null"
            />
            <InputError :message="errors.due_date" />
        </div>
    </div>
</template>
