<script setup lang="ts">
import type { OrderStatus } from '@/types';

defineProps<{
    orderStatus: OrderStatus;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">Colours</h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">
                        Background
                    </dt>
                    <dd
                        class="mt-1 flex items-center gap-2 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        <span
                            class="inline-block h-4 w-4 rounded border"
                            :style="{
                                backgroundColor: orderStatus.background_colour,
                            }"
                        />
                        {{ orderStatus.background_colour }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Text</dt>
                    <dd
                        class="mt-1 flex items-center gap-2 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        <span
                            class="inline-block h-4 w-4 rounded border"
                            :style="{
                                backgroundColor: orderStatus.text_colour,
                            }"
                        />
                        {{ orderStatus.text_colour }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Preview</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        <span
                            class="inline-block rounded px-2 py-1 text-xs font-medium"
                            :style="{
                                backgroundColor: orderStatus.background_colour,
                                color: orderStatus.text_colour,
                            }"
                        >
                            {{ orderStatus.title }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
