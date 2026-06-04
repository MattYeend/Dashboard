<script setup lang="ts">
interface Assignee {
    id: number
    name: string
}

interface Task {
    assignee: Assignee | null
}

defineProps<{
    task: Task
}>()
</script>

<template>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Assignment</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Assigned To</dt>
                <dd class="col-sm-9">{{ task.assignee?.name ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</template>