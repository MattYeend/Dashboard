<script setup lang="ts">
import type { TaskStatus } from '@/types';

defineProps<{
    taskStatus: TaskStatus;
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
                        {{ taskStatus.title }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Description
                    </dt>
                    <dd
                        class="text-gray-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ taskStatus.description ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
