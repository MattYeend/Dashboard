<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    label: string;
    value: number | string;
    icon?: Component;
    hint?: string;
    description?: string | null;
}>();
</script>

<template>
    <div
        class="relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
    >
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-400">{{ label }}</span>
            <component :is="icon" v-if="icon" class="size-4 text-gray-400" />
        </div>
        <span class="mt-2 text-2xl font-semibold text-gray-300">
            {{ value }}
        </span>
        <span v-if="hint" class="mt-1 text-xs text-gray-400">{{ hint }}</span>
        <span v-if="description" class="mt-1 text-xs text-gray-400">
            {{ description }}
        </span>
    </div>
</template>
