<script setup lang="ts">
interface UserOption {
    id: number
    name: string
}

interface Form {
    assigned_to: number | null
}

interface Errors {
    assigned_to?: string
}

defineProps<{
    form: Form
    errors: Errors
    users: UserOption[]
}>()
</script>

<template>
    <div>
        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assigned To</label>
            <select
                id="assigned_to"
                v-model="form.assigned_to"
                class="form-select"
                :class="{ 'is-invalid': errors.assigned_to }"
            >
                <option :value="null">-- Unassigned --</option>
                <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                >
                    {{ user.name }}
                </option>
            </select>
            <div v-if="errors.assigned_to" class="invalid-feedback">{{ errors.assigned_to }}</div>
        </div>
    </div>
</template>