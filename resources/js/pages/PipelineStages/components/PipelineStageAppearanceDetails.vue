<script setup lang="ts">
import type { PipelineStage } from '@/types';

defineProps<{
    stage: PipelineStage;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Appearance
            </h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Preview</dt>
                    <dd class="mt-1 sm:col-span-2 sm:mt-0">
                        <span
                            class="inline-block rounded px-2 py-1 text-sm font-medium"
                            :style="{
                                backgroundColor: stage.background_colour,
                                color: stage.text_colour,
                            }"
                        >
                            {{ stage.title }}
                        </span>
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">
                        Background colour
                    </dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ stage.background_colour }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">
                        Text colour
                    </dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ stage.text_colour }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
