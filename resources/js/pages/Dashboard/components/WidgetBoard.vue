<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pencil, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import CreateWidgetDialog from '@/pages/Dashboard/components/CreateWidgetDialog.vue';
import DashboardWidgetComponent from '@/pages/Dashboard/components/DashboardWidget.vue';
import {
    update as updateCustomWidget,
    destroy as destroyCustomWidget,
} from '@/routes/dashboard/custom-widgets';
import { update as updateWidgets } from '@/routes/dashboard/widgets';
import type { DashboardMetric, DashboardStats, DashboardWidget } from '@/types';

const props = defineProps<{
    widgets: DashboardWidget[];
    stats: DashboardStats;
    metrics: DashboardMetric[];
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

async function deleteCustomWidget(widget: DashboardWidget): Promise<void> {
    if (widget.type !== 'custom' || !widget.id) {
        return;
    }

    await window.axios.delete(
        destroyCustomWidget.url({ customDashboardWidget: widget.id }),
    );

    layout.value = layout.value.filter((w) => w.key !== widget.key);
    reindexPositions();
}

async function persist(): Promise<void> {
    const builtIn = layout.value.filter((widget) => widget.type === 'builtin');
    const custom = layout.value.filter((widget) => widget.type === 'custom');

    await window.axios.put(updateWidgets.url(), {
        widgets: builtIn.map((widget) => ({
            key: widget.key,
            position: widget.position,
            is_visible: widget.is_visible,
        })),
    });

    await Promise.all(
        custom.map((widget) =>
            widget.id
                ? window.axios.put(
                      updateCustomWidget.url({
                          customDashboardWidget: widget.id,
                      }),
                      {
                          position: widget.position,
                          is_visible: widget.is_visible,
                      },
                  )
                : Promise.resolve(),
        ),
    );
}

function toggleEditing(): void {
    if (isEditing.value) {
        reindexPositions();
        persist();
    }

    isEditing.value = !isEditing.value;
}

function onWidgetCreated(): void {
    router.reload({ only: ['widgets'] });
}
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-300">Overview</h2>
            <div class="flex items-center gap-2">
                <CreateWidgetDialog
                    :metrics="metrics"
                    @created="onWidgetCreated"
                />
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm text-gray-300 dark:border-sidebar-border"
                    @click="toggleEditing"
                >
                    <Pencil class="size-4" />
                    {{ isEditing ? 'Done' : 'Edit layout' }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="widget in visibleWidgets"
                :key="widget.key"
                :draggable="isEditing"
                class="relative"
                :class="{
                    'sm:col-span-2 lg:col-span-4':
                        widget.key === 'latest_posts',
                }"
                @dragstart="onDragStart(widget.key)"
                @dragover.prevent
                @drop="onDrop(widget.key)"
            >
                <div
                    v-if="isEditing"
                    class="absolute -top-2 -right-2 z-10 flex gap-1"
                >
                    <button
                        v-if="widget.type === 'custom'"
                        type="button"
                        class="rounded-full border border-sidebar-border/70 bg-background p-1 text-gray-400 dark:border-sidebar-border"
                        @click="deleteCustomWidget(widget)"
                    >
                        <Trash2 class="size-3" />
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-sidebar-border/70 bg-background p-1 text-gray-400 dark:border-sidebar-border"
                        @click="hideWidget(widget.key)"
                    >
                        <X class="size-3" />
                    </button>
                </div>
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
