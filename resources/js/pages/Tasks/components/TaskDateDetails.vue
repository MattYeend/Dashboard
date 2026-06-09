<script setup lang="ts">
import type { Task } from '@/types';

defineProps<{
    task: Task;
}>();

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-grey-900 text-lg leading-6 font-medium">Dates</h3>
        </div>
        <div class="border-grey-200 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Due Date</dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ formatDate(task.due_date) }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Assigned Date
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ formatDate(task.assigned_date) }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Created At
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ formatDate(task.created_at) }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Updated At
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ formatDate(task.updated_at) }}
                    </dd>
                </div>
                <template v-if="task.deleted_at">
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-grey-500 text-sm font-medium">
                            Deleted At
                        </dt>
                        <dd
                            class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                        >
                            {{ formatDate(task.deleted_at) }}
                        </dd>
                    </div>
                </template>
                <template v-if="task.restored_at">
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-grey-500 text-sm font-medium">
                            Restored At
                        </dt>
                        <dd
                            class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                        >
                            {{ formatDate(task.restored_at) }}
                        </dd>
                    </div>
                </template>
            </dl>
        </div>
    </div>
</template>
