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
    <div>
        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assigned To</label>
            <select
                id="assigned_to"
                :value="form.assigned_to"
                class="form-select"
                :class="{ 'is-invalid': errors.assigned_to }"
                @change="
                    updateAssignedTo(($event.target as HTMLSelectElement).value)
                "
            >
                <option :value="null">-- Unassigned --</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <div v-if="errors.assigned_to" class="invalid-feedback">
                {{ errors.assigned_to }}
            </div>
        </div>
    </div>
</template>
