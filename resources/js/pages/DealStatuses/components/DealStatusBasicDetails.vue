<script setup lang="ts">
import type { DealStatus } from '@/types';

interface Props {
    dealStatus: DealStatus;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm font-medium text-gray-300">
                    {{ dealStatus.title }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">
                    {{ dealStatus.description ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Background colour</dt>
                <dd class="flex items-center gap-2 text-sm">
                    <span
                        class="inline-block h-4 w-4 rounded-full border"
                        :style="{
                            backgroundColor: dealStatus.background_colour,
                        }"
                    />
                    {{ dealStatus.background_colour }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Text colour</dt>
                <dd class="flex items-center gap-2 text-sm">
                    <span
                        class="inline-block h-4 w-4 rounded-full border"
                        :style="{ backgroundColor: dealStatus.text_colour }"
                    />
                    {{ dealStatus.text_colour }}
                </dd>
            </div>
        </dl>
    </div>
</template>
