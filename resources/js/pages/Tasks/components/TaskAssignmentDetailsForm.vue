<script setup lang="ts">
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
            <label
                for="assigned_to"
                class="block text-sm font-medium text-gray-700"
            >
                Assigned To
            </label>
            <select
                id="assigned_to"
                :value="assignedTo"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                @change="
                    assignedTo = ($event.target as HTMLSelectElement).value
                        ? Number(($event.target as HTMLSelectElement).value)
                        : null
                "
            >
                <option :value="null">-- Unassigned --</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <p v-if="errors.assigned_to" class="mt-1 text-sm text-red-600">
                {{ errors.assigned_to }}
            </p>
        </div>
    </div>
</template>
