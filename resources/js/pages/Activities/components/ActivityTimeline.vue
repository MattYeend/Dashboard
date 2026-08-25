<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import ActivityItem from '@/pages/Activities/components/ActivityItem.vue';
import ActivityNoteForm from '@/pages/Activities/components/ActivityNoteForm.vue';
import activitiesRoutes, {
    index as activitiesIndex,
} from '@/routes/activities';
import type { Activity } from '@/types';

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

interface TimelineMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const activities = ref<Activity[]>([]);
const meta = ref<TimelineMeta | null>(null);
const loading = ref(false);

const filters = reactive({
    type: 'all',
    search: '',
});

const columns: ResourceTableColumn[] = [
    { key: 'type', label: 'Type' },
    { key: 'description', label: 'Description' },
    { key: 'occurred_at', label: 'Occurred at' },
    { key: 'creator', label: 'Logged by' },
];

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function truncate(value: string | null, length = 20): string {
    if (!value) {
        return '-';
    }

    return value.length > length ? `${value.slice(0, length)}…` : value;
}

async function fetchActivities(page = 1): Promise<void> {
    loading.value = true;

    try {
        const response = await axios.get(activitiesIndex.url(), {
            params: {
                activityable_type: props.activityableType,
                activityable_id: props.activityableId,
                type: filters.type === 'all' ? undefined : filters.type,
                search: filters.search || undefined,
                page,
            },
        });

        activities.value = response.data.activities.data;
        meta.value = response.data.activities.meta;
    } finally {
        loading.value = false;
    }
}

const exportUrl = computed(() => {
    const params = new URLSearchParams({
        activityable_type: props.activityableType,
        activityable_id: String(props.activityableId),
    });

    return `${activitiesRoutes.export.url()}?${params.toString()}`;
});

watch(filters, () => fetchActivities(1));

onMounted(() => fetchActivities());
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

        <div class="mb-4 flex flex-wrap gap-2">
            <Select v-model="filters.type">
                <SelectTrigger class="w-40 border-gray-500 text-gray-300">
                    <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All types</SelectItem>
                    <SelectItem value="note">Note</SelectItem>
                    <SelectItem value="status_change">Status change</SelectItem>
                    <SelectItem value="task_created">Task created</SelectItem>
                    <SelectItem value="call_logged">Call logged</SelectItem>
                    <SelectItem value="email_logged">Email logged</SelectItem>
                </SelectContent>
            </Select>
            <Input
                v-model="filters.search"
                placeholder="Search description..."
                class="max-w-xs border-gray-500 text-gray-300"
            />
        </div>

        <ActivityNoteForm
            v-if="canCreate"
            :activityable-type="activityableType"
            :activityable-id="activityableId"
            class="mb-4"
            @created="fetchActivities(meta?.current_page ?? 1)"
        />

        <ResourceTable
            :rows="activities"
            :columns="columns"
            row-key="id"
            :empty-message="loading ? 'Loading...' : 'No activity yet.'"
            class="text-xs [&_table]:table-fixed [&_td]:py-1.5 [&_td]:break-words [&_td]:whitespace-normal [&_th]:py-1.5"
        >
            <template #cell-type="{ row }">
                <ActivityItem :activity="row" />
            </template>
            <template #cell-description="{ row }">
                <span :title="row.description ?? undefined">{{
                    truncate(row.description)
                }}</span>
            </template>
            <template #cell-occurred_at="{ row }">
                {{ formatDateTime(row.occurred_at) }}
            </template>
            <template #cell-creator="{ row }">
                {{ row.creator?.name ?? 'System' }}
            </template>
        </ResourceTable>

        <div
            v-if="meta && meta.last_page > 1"
            class="mt-4 flex items-center justify-between text-sm text-gray-400"
        >
            <span>{{ meta.from }}-{{ meta.to }} of {{ meta.total }}</span>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="meta.current_page <= 1"
                    @click="fetchActivities(meta.current_page - 1)"
                >
                    Previous
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="fetchActivities(meta.current_page + 1)"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
