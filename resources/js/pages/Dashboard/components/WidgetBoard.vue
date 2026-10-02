<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useWidgetLayout } from '@/composables/useWidgetLayout';
import HiddenWidgetList from '@/pages/Dashboard/components/HiddenWidgetList.vue';
import WidgetBoardHeader from '@/pages/Dashboard/components/WidgetBoardHeader.vue';
import WidgetBoardItem from '@/pages/Dashboard/components/WidgetBoardItem.vue';
import type { DashboardMetric, DashboardStats, DashboardWidget } from '@/types';

const props = defineProps<{
    widgets: DashboardWidget[];
    stats: DashboardStats;
    metrics: DashboardMetric[];
}>();

const {
    isEditing,
    saveError,
    visibleWidgets,
    hiddenWidgets,
    onDragStart,
    onDrop,
    showWidget,
    hideWidget,
    deleteCustomWidget,
    toggleEditing,
} = useWidgetLayout(() => props.widgets);

function onWidgetCreated(): void {
    router.reload({ only: ['widgets'] });
}
</script>

<template>
    <div>
        <WidgetBoardHeader
            :metrics="metrics"
            :is-editing="isEditing"
            @created="onWidgetCreated"
            @toggle="toggleEditing"
        />

        <p v-if="saveError" class="mb-4 text-sm text-red-500" role="alert">
            {{ saveError }}
        </p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <WidgetBoardItem
                v-for="widget in visibleWidgets"
                :key="widget.key"
                :widget="widget"
                :stats="stats"
                :is-editing="isEditing"
                @dragstart="onDragStart"
                @drop="onDrop"
                @hide="hideWidget"
                @delete="deleteCustomWidget"
            />
        </div>

        <HiddenWidgetList
            v-if="isEditing && hiddenWidgets.length"
            :widgets="hiddenWidgets"
            @show="showWidget"
        />
    </div>
</template>
