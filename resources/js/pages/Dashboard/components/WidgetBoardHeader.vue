<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';
import CreateWidgetDialog from '@/pages/Dashboard/components/CreateWidgetDialog.vue';
import type { DashboardMetric } from '@/types';

defineProps<{
    metrics: DashboardMetric[];
    isEditing: boolean;
}>();

const emit = defineEmits<{
    created: [];
    toggle: [];
}>();
</script>

<template>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-300">Overview</h2>
        <div class="flex items-center gap-2">
            <CreateWidgetDialog :metrics="metrics" @created="emit('created')" />
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm text-gray-300 dark:border-sidebar-border"
                @click="emit('toggle')"
            >
                <Pencil class="size-4" />
                {{ isEditing ? 'Done' : 'Edit layout' }}
            </button>
        </div>
    </div>
</template>
