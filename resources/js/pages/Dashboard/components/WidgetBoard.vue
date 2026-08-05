<script setup lang="ts">
import { Pencil, Plus, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DashboardStats, DashboardWidget } from '@/types';
import DashboardWidgetComponent from '@/pages/Dashboard/components/DashboardWidget.vue';
import { update as updateWidgets } from '@/routes/dashboard/widgets';

const props = defineProps<{
    widgets: DashboardWidget[];
    stats: DashboardStats;
}>();

const layout = ref<DashboardWidget[]>(
    [...props.widgets].sort((a, b) => a.position - b.position),
);
const isEditing = ref(false);
const draggedKey = ref<string | null>(null);

const visibleWidgets = computed(() =>
    layout.value.filter((widget) => widget.is_visible),
);
const hiddenWidgets = computed(() =>
    layout.value.filter((widget) => !widget.is_visible),
);

function onDragStart(key: string): void {
    draggedKey.value = key;
}

function onDrop(targetKey: string): void {
    if (!draggedKey.value || draggedKey.value === targetKey) {
        return;
    }

    const fromIndex = layout.value.findIndex((w) => w.key === draggedKey.value);
    const toIndex = layout.value.findIndex((w) => w.key === targetKey);

    if (fromIndex === -1 || toIndex === -1) {
        return;
    }

    const [moved] = layout.value.splice(fromIndex, 1);
    layout.value.splice(toIndex, 0, moved);

    draggedKey.value = null;
    reindexPositions();
}

function reindexPositions(): void {
    layout.value = layout.value.map((widget, index) => ({
        ...widget,
        position: index,
    }));
}

function showWidget(key: string): void {
    layout.value = layout.value.map((widget) =>
        widget.key === key ? { ...widget, is_visible: true } : widget,
    );
    reindexPositions();
    persist();
}

function hideWidget(key: string): void {
    layout.value = layout.value.map((widget) =>
        widget.key === key ? { ...widget, is_visible: false } : widget,
    );
    persist();
}

function persist(): void {
    fetch(updateWidgets.url(), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') ?? '',
        },
        body: JSON.stringify({
            widgets: layout.value.map((widget) => ({
                key: widget.key,
                position: widget.position,
                is_visible: widget.is_visible,
            })),
        }),
    });
}

function toggleEditing(): void {
    if (isEditing.value) {
        reindexPositions();
        persist();
    }

    isEditing.value = !isEditing.value;
}
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-300">Overview</h2>
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm text-gray-300 dark:border-sidebar-border"
                @click="toggleEditing"
            >
                <Pencil class="size-4" />
                {{ isEditing ? 'Done' : 'Edit layout' }}
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="widget in visibleWidgets"
                :key="widget.key"
                :draggable="isEditing"
                class="relative"
                :class="{ 'sm:col-span-2 lg:col-span-4': widget.key === 'latest_posts' }"
                @dragstart="onDragStart(widget.key)"
                @dragover.prevent
                @drop="onDrop(widget.key)"
            >
                <button
                    v-if="isEditing"
                    type="button"
                    class="absolute -top-2 -right-2 z-10 rounded-full border border-sidebar-border/70 bg-background p-1 text-gray-400 dark:border-sidebar-border"
                    @click="hideWidget(widget.key)"
                >
                    <X class="size-3" />
                </button>
                <DashboardWidgetComponent :widget="widget" :stats="stats" />
            </div>
        </div>

        <div v-if="isEditing && hiddenWidgets.length" class="mt-6">
            <span class="mb-2 block text-sm font-medium text-gray-400"
                >Add a widget</span
            >
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="widget in hiddenWidgets"
                    :key="widget.key"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm text-gray-300 dark:border-sidebar-border"
                    @click="showWidget(widget.key)"
                >
                    <Plus class="size-3" />
                    {{ widget.label }}
                </button>
            </div>
        </div>
    </div>
</template>
