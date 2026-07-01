<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/vue3';
import type { UserOption } from '@/types';

interface TaskFormData {
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
}

interface Props {
    users: UserOption[];
    errors: Partial<InertiaFormProps<TaskFormData>['errors']>;
}

defineProps<Props>();

const assignedTo = defineModel<number | null>('assignedTo', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="assigned_to">Assigned To</Label>
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
    </div>
</template>
