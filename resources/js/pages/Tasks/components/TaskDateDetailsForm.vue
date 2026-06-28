<script setup lang="ts">
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
            <label
                for="due_date"
                class="text-gray-700 block text-sm font-medium"
            >
                Due Date
            </label>
            <input
                id="due_date"
                :value="dueDate ?? ''"
                type="date"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="
                    dueDate = ($event.target as HTMLInputElement).value || null
                "
            />
            <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">
                {{ errors.due_date }}
            </p>
        </div>

        <div>
            <label
                for="assigned_date"
                class="text-gray-700 block text-sm font-medium"
            >
                Assigned Date
            </label>
            <input
                id="assigned_date"
                :value="assignedDate ?? ''"
                type="date"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="
                    assignedDate =
                        ($event.target as HTMLInputElement).value || null
                "
            />
            <p v-if="errors.assigned_date" class="mt-1 text-sm text-red-600">
                {{ errors.assigned_date }}
            </p>
        </div>
    </div>
</template>
