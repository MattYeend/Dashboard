<script setup lang="ts">
interface TaskStatus {
    id: number
    title: string
    background_colour: string | null
    text_colour: string | null
}

interface Form {
    title: string
    description: string | null
    status_id: number | null
}

interface Errors {
    title?: string
    description?: string
    status_id?: string
}

defineProps<{
    form: Form
    errors: Errors
    statuses: TaskStatus[]
}>()
</script>

<template>
    <div>
        <div class="mb-3">
            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
            <input
                id="title"
                v-model="form.title"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.title }"
                placeholder="Enter task title"
            />
            <div v-if="errors.title" class="invalid-feedback">{{ errors.title }}</div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea
                id="description"
                v-model="form.description"
                class="form-control"
                :class="{ 'is-invalid': errors.description }"
                rows="4"
                placeholder="Enter task description"
            ></textarea>
            <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
        </div>

        <div class="mb-3">
            <label for="status_id" class="form-label">Status</label>
            <select
                id="status_id"
                v-model="form.status_id"
                class="form-select"
                :class="{ 'is-invalid': errors.status_id }"
            >
                <option :value="null">-- Select a status --</option>
                <option
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.title }}
                </option>
            </select>
            <div v-if="errors.status_id" class="invalid-feedback">{{ errors.status_id }}</div>
        </div>
    </div>
</template>