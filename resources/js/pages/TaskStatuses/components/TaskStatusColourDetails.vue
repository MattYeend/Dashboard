<script setup lang="ts">
import type { TaskStatus } from '@/types';

defineProps<{
    taskStatus: TaskStatus;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-grey-900 text-lg leading-6 font-medium">
                Colours
            </h3>
        </div>
        <div class="border-grey-200 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Background</dt>
                    <dd class="text-grey-900 mt-1 flex items-center gap-2 text-sm sm:col-span-2 sm:mt-0">
                        <span
                            class="inline-block h-4 w-4 rounded border"
                            :style="{ backgroundColor: taskStatus.background_colour }"
                        />
                        {{ taskStatus.background_colour }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Text</dt>
                    <dd class="text-grey-900 mt-1 flex items-center gap-2 text-sm sm:col-span-2 sm:mt-0">
                        <span
                            class="inline-block h-4 w-4 rounded border"
                            :style="{ backgroundColor: taskStatus.text_colour }"
                        />
                        {{ taskStatus.text_colour }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Preview</dt>
                    <dd class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0">
                        <span
                            class="inline-block rounded px-2 py-1 text-xs font-medium"
                            :style="{
                                backgroundColor: taskStatus.background_colour,
                                color: taskStatus.text_colour,
                            }"
                        >
                            {{ taskStatus.title }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>