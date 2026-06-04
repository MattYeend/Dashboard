<script setup lang="ts">
interface Form {
    due_date: string | null
    assigned_date: string | null
}

interface Errors {
    due_date?: string
    assigned_date?: string
}

defineProps<{
    form: Form
    errors: Errors
}>()
</script>

<template>
    <div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input
                id="due_date"
                v-model="form.due_date"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.due_date }"
            />
            <div v-if="errors.due_date" class="invalid-feedback">{{ errors.due_date }}</div>
        </div>

        <div class="mb-3">
            <label for="assigned_date" class="form-label">Assigned Date</label>
            <input
                id="assigned_date"
                v-model="form.assigned_date"
                type="date"
                class="form-control"
                :class="{ 'is-invalid': errors.assigned_date }"
            />
            <div v-if="errors.assigned_date" class="invalid-feedback">{{ errors.assigned_date }}</div>
        </div>
    </div>
</template>