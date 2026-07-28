<script setup lang="ts">
import type { Task } from '@/types';

interface Props {
    task: Task;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Assignment</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Assigned to</dt>
                <dd class="text-sm">
                    {{ task.assignee?.name ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
