<script setup lang="ts">
import type { Task } from '@/types';

defineProps<{
    task: Task;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-gray-900 text-lg leading-6 font-medium">
                Basic Details
            </h3>
        </div>
        <div class="border-grey-200 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Title</dt>
                    <dd
                        class="text-gray-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ task.title }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Description
                    </dt>
                    <dd
                        class="text-gray-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ task.description ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Status</dt>
                    <dd
                        class="text-gray-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
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
                        <span v-else>—</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
