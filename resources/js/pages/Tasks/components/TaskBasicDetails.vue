<script setup lang="ts">
interface TaskStatus {
    id: number
    title: string
    background_colour: string | null
    text_colour: string | null
}

interface Task {
    title: string
    description: string | null
    status: TaskStatus | null
}

defineProps<{
    task: Task
}>()
</script>

<template>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Basic Details</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Title</dt>
                <dd class="col-sm-9">{{ task.title }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ task.description ?? '—' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span
                        v-if="task.status"
                        class="badge"
                        :style="{
                            backgroundColor: task.status.background_colour ?? '#e2e8f0',
                            color: task.status.text_colour ?? '#1a202c',
                        }"
                    >
                        {{ task.status.title }}
                    </span>
                    <span v-else>—</span>
                </dd>
            </dl>
        </div>
    </div>
</template>