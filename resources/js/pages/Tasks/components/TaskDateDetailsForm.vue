<script setup lang="ts">
import type { TaskFormData } from '@/types';

interface Errors {
    due_date?: string;
    assigned_date?: string;
}

const props = defineProps<{
    form: TaskFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: TaskFormData): void;
}>();

function updateDate(field: 'due_date' | 'assigned_date', value: string): void {
    emit('update:form', { ...props.form, [field]: value || null });
}
</script>

<template>
    <div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input
                id="due_date"
                :value="form.due_date ?? ''"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.due_date }"
                @change="updateDate('due_date', ($event.target as HTMLInputElement).value)"
            />
            <div v-if="errors.due_date" class="invalid-feedback">{{ errors.due_date }}</div>
        </div>

        <div class="mb-3">
            <label for="assigned_date" class="form-label">Assigned Date</label>
            <input
                id="assigned_date"
                :value="form.assigned_date ?? ''"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.assigned_date }"
                @change="updateDate('assigned_date', ($event.target as HTMLInputElement).value)"
            />
            <div v-if="errors.assigned_date" class="invalid-feedback">{{ errors.assigned_date }}</div>
        </div>
    </div>
</template>