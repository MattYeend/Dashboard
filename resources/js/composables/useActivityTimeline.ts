import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import activitiesRoutes, {
    index as activitiesIndex,
} from '@/routes/activities';
import type { Activity, Pagination } from '@/types';

interface TimelineSubject {
    activityableType: string;
    activityableId: number;
}

export function useActivityTimeline(subject: () => TimelineSubject) {
    const activities = ref<Activity[]>([]);
    const meta = ref<Pagination | null>(null);
    const loading = ref(false);
    const filters = reactive({ type: 'all', search: '' });

    let latestRequest = 0;

    async function fetchActivities(page = 1): Promise<void> {
        const requestId = ++latestRequest;
        const { activityableType, activityableId } = subject();

        loading.value = true;

        try {
            const response = await axios.get(activitiesIndex.url(), {
                params: {
                    activityable_type: activityableType,
                    activityable_id: activityableId,
                    type: filters.type === 'all' ? undefined : filters.type,
                    search: filters.search || undefined,
                    page,
                },
            });

            if (requestId !== latestRequest) {
                return;
            }

            activities.value = response.data.activities.data;
            meta.value = response.data.activities.meta;
        } finally {
            if (requestId === latestRequest) {
                loading.value = false;
            }
        }
    }

    const debouncedSearch = useDebounceFn(() => fetchActivities(1), 300);

    watch(
        () => filters.type,
        () => fetchActivities(1),
    );
    watch(
        () => filters.search,
        () => debouncedSearch(),
    );

    const exportUrl = computed(() => {
        const { activityableType, activityableId } = subject();

        return activitiesRoutes.export({
            query: {
                activityable_type: activityableType,
                activityable_id: activityableId,
            },
        }).url;
    });

    onMounted(() => fetchActivities());

    return { activities, meta, loading, filters, exportUrl, fetchActivities };
}
