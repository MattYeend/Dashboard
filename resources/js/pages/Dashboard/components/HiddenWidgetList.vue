<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import type { DashboardWidget } from '@/types';

defineProps<{
    widgets: DashboardWidget[];
}>();

const emit = defineEmits<{
    show: [key: string];
}>();
</script>

<template>
    <div class="mt-6">
        <span class="mb-2 block text-sm font-medium text-gray-400"
            >Add a widget</span
        >
        <div class="flex flex-wrap gap-2">
            <button
                v-for="widget in widgets"
                :key="widget.key"
                type="button"
                class="inline-flex items-center gap-1 rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm text-gray-300 dark:border-sidebar-border"
                @click="emit('show', widget.key)"
            >
                <Plus class="size-3" />
                {{ widget.label }}
            </button>
        </div>
    </div>
</template>
