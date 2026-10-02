<script setup lang="ts">
import { Trash2, X } from 'lucide-vue-next';
import DashboardWidgetComponent from '@/pages/Dashboard/components/DashboardWidget.vue';
import type { DashboardStats, DashboardWidget } from '@/types';

defineProps<{
    widget: DashboardWidget;
    stats: DashboardStats;
    isEditing: boolean;
}>();

const emit = defineEmits<{
    dragstart: [key: string];
    drop: [key: string];
    hide: [key: string];
    delete: [widget: DashboardWidget];
}>();
</script>

<template>
    <div
        :draggable="isEditing"
        class="relative"
        :class="{
            'sm:col-span-2 lg:col-span-4': widget.key === 'latest_posts',
        }"
        @dragstart="emit('dragstart', widget.key)"
        @dragover.prevent
        @drop="emit('drop', widget.key)"
    >
        <div v-if="isEditing" class="absolute -top-2 -right-2 z-10 flex gap-1">
            <button
                v-if="widget.type === 'custom'"
                type="button"
                class="rounded-full border border-sidebar-border/70 p-1 text-gray-400 dark:border-sidebar-border"
                :aria-label="`Delete ${widget.label}`"
                @click="emit('delete', widget)"
            >
                <Trash2 class="size-3" />
            </button>
            <button
                type="button"
                class="rounded-full border border-sidebar-border/70 p-1 text-gray-400 dark:border-sidebar-border"
                :aria-label="`Hide ${widget.label}`"
                @click="emit('hide', widget.key)"
            >
                <X class="size-3" />
            </button>
        </div>
        <DashboardWidgetComponent :widget="widget" :stats="stats" />
    </div>
</template>
