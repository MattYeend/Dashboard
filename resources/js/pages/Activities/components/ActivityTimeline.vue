<script setup lang="ts">
import { useActivityTimeline } from '@/composables/useActivityTimeline';
import ActivityNoteForm from '@/pages/Activities/components/ActivityNoteForm.vue';
import ActivityTimelineFilters from '@/pages/Activities/components/ActivityTimelineFilters.vue';
import ActivityTimelinePagination from '@/pages/Activities/components/ActivityTimelinePagination.vue';
import ActivityTimelineTable from '@/pages/Activities/components/ActivityTimelineTable.vue';

interface Props {
    activityableType: string;
    activityableId: number;
    canCreate?: boolean;
    canExport?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    canCreate: false,
    canExport: false,
});

const { activities, meta, loading, filters, exportUrl, fetchActivities } =
    useActivityTimeline(() => ({
        activityableType: props.activityableType,
        activityableId: props.activityableId,
    }));
</script>

<template>
    <div class="rounded-lg border border-gray-500 p-4">
        <div class="mb-4 flex items-center justify-between gap-2">
            <h2 class="text-sm font-medium text-gray-400">Activity timeline</h2>
            <a
                v-if="canExport"
                :href="exportUrl"
                class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-gray-300 hover:text-white"
            >
                Export CSV
            </a>
        </div>

        <ActivityTimelineFilters
            v-model:type="filters.type"
            v-model:search="filters.search"
        />

        <ActivityNoteForm
            v-if="canCreate"
            :activityable-type="activityableType"
            :activityable-id="activityableId"
            class="mb-4"
            @created="fetchActivities(meta?.current_page ?? 1)"
        />

        <ActivityTimelineTable :activities="activities" :loading="loading" />

        <ActivityTimelinePagination
            v-if="meta && meta.last_page > 1"
            :meta="meta"
            @change="fetchActivities"
        />
    </div>
</template>
