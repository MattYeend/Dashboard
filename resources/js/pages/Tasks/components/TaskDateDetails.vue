<script setup lang="ts">
interface Task {
    due_date: string | null
    assigned_date: string | null
    created_at: string
    updated_at: string
    deleted_at: string | null
    restored_at: string | null
}

defineProps<{
    task: Task
}>()

function formatDate(value: string | null): string {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}
</script>

<template>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Dates</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Due Date</dt>
                <dd class="col-sm-9">{{ formatDate(task.due_date) }}</dd>

                <dt class="col-sm-3">Assigned Date</dt>
                <dd class="col-sm-9">{{ formatDate(task.assigned_date) }}</dd>

                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9">{{ formatDate(task.created_at) }}</dd>

                <dt class="col-sm-3">Updated At</dt>
                <dd class="col-sm-9">{{ formatDate(task.updated_at) }}</dd>

                <template v-if="task.deleted_at">
                    <dt class="col-sm-3">Deleted At</dt>
                    <dd class="col-sm-9">{{ formatDate(task.deleted_at) }}</dd>
                </template>

                <template v-if="task.restored_at">
                    <dt class="col-sm-3">Restored At</dt>
                    <dd class="col-sm-9">{{ formatDate(task.restored_at) }}</dd>
                </template>
            </dl>
        </div>
    </div>
</template>