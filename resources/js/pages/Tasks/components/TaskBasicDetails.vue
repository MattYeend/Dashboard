<script setup lang="ts">
import type { Task } from '@/types';

interface Props {
    task: Task;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm">
                    {{ task.title }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">
                    {{ task.description ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span
                        v-if="task.status"
                        :style="{
                            backgroundColor:
                                task.status.background_colour ?? '#e2e8f0',
                            color: task.status.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ task.status.title }}
                    </span>
                    <span v-else>-</span>
                </dd>
            </div>
        </dl>
    </div>
</template>
