<script setup lang="ts">
import type { TaskFormData, UserOption } from '@/types';

interface Errors {
    assigned_to?: string;
}

const props = defineProps<{
    form: TaskFormData;
    errors: Errors;
    users: UserOption[];
}>();

const emit = defineEmits<{
    (e: 'update:form', value: TaskFormData): void;
}>();

function updateAssignedTo(value: string): void {
    emit('update:form', {
        ...props.form,
        assigned_to: value ? Number(value) : null,
    });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="assigned_to" class="text-grey-700 block text-sm font-medium">
                Assigned To
            </label>
            <select
                id="assigned_to"
                :value="form.assigned_to"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="updateAssignedTo(($event.target as HTMLSelectElement).value)"
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