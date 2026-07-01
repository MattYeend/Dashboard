<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { InertiaFormProps } from '@inertiajs/vue3';

interface TaskFormData {
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
}

interface Props {
    errors: Partial<InertiaFormProps<TaskFormData>['errors']>;
}

defineProps<Props>();

const dueDate = defineModel<string | null>('dueDate', { default: null });
const assignedDate = defineModel<string | null>('assignedDate', {
    default: null,
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="due_date">Due Date</Label>
            <Input
                id="due_date"
                :model-value="dueDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="dueDate = ($event as string) || null"
            />
            <InputError :message="errors.due_date" />
        </div>

        <div>
            <Label for="assigned_date">Assigned Date</Label>
            <Input
                id="assigned_date"
                :model-value="assignedDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="assignedDate = ($event as string) || null"
            />
            <InputError :message="errors.assigned_date" />
        </div>
    </div>
</template>
