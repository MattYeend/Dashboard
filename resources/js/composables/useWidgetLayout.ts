import axios from 'axios';
import { computed, ref, watch } from 'vue';
import {
    destroy as destroyCustomWidget,
    update as updateCustomWidget,
} from '@/routes/dashboard/custom-widgets';
import { update as updateWidgets } from '@/routes/dashboard/widgets';
import type { DashboardWidget } from '@/types';

const SAVE_ERROR = 'The dashboard layout could not be saved. Please try again.';
const DELETE_ERROR = 'The widget could not be deleted. Please try again.';

function sortByPosition(widgets: DashboardWidget[]): DashboardWidget[] {
    return [...widgets].sort((a, b) => a.position - b.position);
}

export function useWidgetLayout(source: () => DashboardWidget[]) {
    const layout = ref<DashboardWidget[]>(sortByPosition(source()));
    const isEditing = ref(false);
    const draggedKey = ref<string | null>(null);
    const saveError = ref<string | null>(null);

    const visibleWidgets = computed(() =>
        layout.value.filter((widget) => widget.is_visible),
    );
    const hiddenWidgets = computed(() =>
        layout.value.filter((widget) => !widget.is_visible),
    );

    watch(source, (widgets) => {
        layout.value = sortByPosition(widgets);
    });

    function reindexPositions(): void {
        layout.value = layout.value.map((widget, index) => ({
            ...widget,
            position: index,
        }));
    }

    function onDragStart(key: string): void {
        draggedKey.value = key;
    }

    function onDrop(targetKey: string): void {
        if (!draggedKey.value || draggedKey.value === targetKey) {
            return;
        }

        const fromIndex = layout.value.findIndex(
            (widget) => widget.key === draggedKey.value,
        );
        const toIndex = layout.value.findIndex(
            (widget) => widget.key === targetKey,
        );

        if (fromIndex === -1 || toIndex === -1) {
            return;
        }

        const [moved] = layout.value.splice(fromIndex, 1);
        layout.value.splice(toIndex, 0, moved);

        draggedKey.value = null;
        reindexPositions();
    }

    function setVisibility(key: string, isVisible: boolean): void {
        layout.value = layout.value.map((widget) =>
            widget.key === key ? { ...widget, is_visible: isVisible } : widget,
        );
    }

    async function persist(): Promise<void> {
        const builtIn = layout.value.filter(
            (widget) => widget.type === 'builtin',
        );
        const custom = layout.value.filter(
            (widget) => widget.type === 'custom',
        );

        try {
            await axios.put(updateWidgets.url(), {
                widgets: builtIn.map((widget) => ({
                    key: widget.key,
                    position: widget.position,
                    is_visible: widget.is_visible,
                })),
            });

            await Promise.all(
                custom.flatMap((widget) =>
                    widget.id
                        ? [
                              axios.put(
                                  updateCustomWidget.url({
                                      customDashboardWidget: widget.id,
                                  }),
                                  {
                                      position: widget.position,
                                      is_visible: widget.is_visible,
                                  },
                              ),
                          ]
                        : [],
                ),
            );

            saveError.value = null;
        } catch {
            saveError.value = SAVE_ERROR;
        }
    }

    async function showWidget(key: string): Promise<void> {
        setVisibility(key, true);
        reindexPositions();
        await persist();
    }

    async function hideWidget(key: string): Promise<void> {
        setVisibility(key, false);
        await persist();
    }

    async function deleteCustomWidget(widget: DashboardWidget): Promise<void> {
        if (widget.type !== 'custom' || !widget.id) {
            return;
        }

        try {
            await axios.delete(
                destroyCustomWidget.url({ customDashboardWidget: widget.id }),
            );

            layout.value = layout.value.filter((w) => w.key !== widget.key);
            reindexPositions();
            saveError.value = null;
        } catch {
            saveError.value = DELETE_ERROR;
        }
    }

    async function toggleEditing(): Promise<void> {
        const wasEditing = isEditing.value;

        isEditing.value = !wasEditing;

        if (wasEditing) {
            reindexPositions();
            await persist();
        }
    }

    return {
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
    };
}
