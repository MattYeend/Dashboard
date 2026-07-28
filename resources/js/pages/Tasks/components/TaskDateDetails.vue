<script setup lang="ts">
import type { Task } from '@/types';

interface Props {
    task: Task;
}

defineProps<Props>();

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Dates</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Due date</dt>
                <dd class="text-sm">
                    {{ formatDate(task.due_date) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Assigned date</dt>
                <dd class="text-sm">
                    {{ formatDate(task.assigned_date) }}
                </dd>
            </div>
        </dl>
    </div>
</template>
