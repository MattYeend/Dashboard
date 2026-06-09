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
    <div class="space-y-4">
        <div>
            <label
                for="due_date"
                class="text-grey-700 block text-sm font-medium"
            >
                Due Date
            </label>
            <input
                id="due_date"
                :value="form.due_date ?? ''"
                type="date"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="
                    updateDate(
                        'due_date',
                        ($event.target as HTMLInputElement).value,
                    )
                "
            />
            <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">
                {{ errors.due_date }}
            </p>
        </div>

        <div>
            <label
                for="assigned_date"
                class="text-grey-700 block text-sm font-medium"
            >
                Assigned Date
            </label>
            <input
                id="assigned_date"
                :value="form.assigned_date ?? ''"
                type="date"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="
                    updateDate(
                        'assigned_date',
                        ($event.target as HTMLInputElement).value,
                    )
                "
            />
            <p v-if="errors.assigned_date" class="mt-1 text-sm text-red-600">
                {{ errors.assigned_date }}
            </p>
        </div>
    </div>
</template>
