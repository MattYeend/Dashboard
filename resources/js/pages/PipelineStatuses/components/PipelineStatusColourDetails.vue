<script setup lang="ts">
import type { PipelineStatus } from '@/types';

interface Props {
    pipelineStatus: PipelineStatus;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Colours</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Background</dt>
                <dd class="flex items-center gap-2 text-sm">
                    <span
                        class="inline-block h-4 w-4 rounded border"
                        :style="{
                            backgroundColor: pipelineStatus.background_colour,
                        }"
                    />
                    {{ pipelineStatus.background_colour }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Text</dt>
                <dd class="flex items-center gap-2 text-sm">
                    <span
                        class="inline-block h-4 w-4 rounded border"
                        :style="{
                            backgroundColor: pipelineStatus.text_colour,
                        }"
                    />
                    {{ pipelineStatus.text_colour }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Preview</dt>
                <dd class="text-sm">
                    <span
                        class="inline-block rounded px-2 py-1 text-xs font-medium"
                        :style="{
                            backgroundColor: pipelineStatus.background_colour,
                            color: pipelineStatus.text_colour,
                        }"
                    >
                        {{ pipelineStatus.title }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>
</template>
