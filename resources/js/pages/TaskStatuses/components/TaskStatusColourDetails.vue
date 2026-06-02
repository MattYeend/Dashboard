<script setup lang="ts">
import type { TaskStatus } from '@/types';
defineProps<{ taskStatus: TaskStatus }>();
</script>

<template>
    <div class="space-y-2">
        <h2 class="text-sm font-medium tracking-wide uppercase">Colours</h2>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="font-medium">Background</dt>
            <dd class="items-centre flex gap-2">
                <span
                    class="inline-block h-4 w-4 rounded border"
                    :style="{ backgroundColor: taskStatus.background_colour }"
                />
                {{ taskStatus.background_colour }}
            </dd>
            <dt class="font-medium">Text</dt>
            <dd class="items-centre flex gap-2">
                <span
                    class="inline-block h-4 w-4 rounded border"
                    :style="{ backgroundColor: taskStatus.text_colour }"
                />
                {{ taskStatus.text_colour }}
            </dd>
            <dt class="font-medium">Preview</dt>
            <dd>
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
        </dl>
    </div>
</template>
