```vue
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import activityLogs, { index, destroy } from '@/routes/activity-logs';
import type {
    ActivityLog,
    Pagination as PaginationType,
    PaginationLink,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Activity Logs', href: index().url }],
    },
});

const props = defineProps<{
    logs: {
        data: ActivityLog[];
        meta: PaginationType;
        links: PaginationLink[];
    };
    filters: Record<string, string | null>;
    permissions_meta: {
        can_view_any: boolean;
        can_export: boolean;
        can_delete: boolean;
    };
}>();

const selected = ref<number[]>([]);
const confirmingDeleteId = ref<number | null>(null);
const confirmingBulkDelete = ref(false);

const filterValues = ref<Record<string, string>>({
    search: props.filters.search ?? '',
});

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search activity logs...',
    },
];

const columns = [
    { key: 'action_label', label: 'Action' },
    { key: 'logged_in_user', label: 'Performed by' },
    { key: 'related_to_user', label: 'Related to' },
    { key: 'created_at', label: 'Date' },
];

function applyFilters(): void {
    router.get(
        index().url,
        {
            ...filterValues.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function confirmDelete(id: number): void {
    confirmingDeleteId.value = id;
}

function performDelete(): void {
    if (confirmingDeleteId.value === null) {
        return;
    }

    router.delete(destroy(confirmingDeleteId.value).url, {
        preserveScroll: true,
        onFinish: () => {
            confirmingDeleteId.value = null;
        },
    });
}

function performBulkDelete(): void {
    if (selected.value.length === 0) {
        return;
    }

    router.post(
        activityLogs.bulk.delete().url,
        {
            ids: selected.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                confirmingBulkDelete.value = false;
                selected.value = [];
            },
        },
    );
}
</script>

<template>
    <Head title="Activity Logs" />

    <IndexHeader title="Activity Logs" :can-create="false" />

    <FilterBar
        v-model="filterValues"
        :fields="filterFields"
        @change="applyFilters"
    />

    <div class="mb-4 flex justify-end gap-2">
        <a
            v-if="permissions_meta.can_export"
            :href="activityLogs.export({ query: filters }).url"
            class="text-sm text-gray-300 underline"
        >
            Export CSV
        </a>

        <button
            v-if="permissions_meta.can_delete && selected.length > 0"
            type="button"
            class="text-sm text-red-400 underline"
            @click="confirmingBulkDelete = true"
        >
            Delete selected ({{ selected.length }})
        </button>
    </div>

    <ResourceTable
        v-model:selected="selected"
        :rows="logs.data"
        :columns="columns"
        :selectable="permissions_meta.can_delete"
    >
        <template #cell-logged_in_user="{ row }">
            {{ row.logged_in_user?.name ?? 'System' }}
        </template>

        <template #cell-related_to_user="{ row }">
            {{ row.related_to_user?.name ?? 'N/A' }}
        </template>

        <template #cell-created_at="{ row }">
            {{ new Date(row.created_at).toLocaleString('en-GB') }}
        </template>

        <template #actions="{ row }">
            <button
                v-if="permissions_meta.can_delete"
                type="button"
                class="text-sm text-red-400 underline"
                @click="confirmDelete(row.id)"
            >
                Delete
            </button>
        </template>
    </ResourceTable>

    <Pagination
        :meta="logs.meta"
        :links="logs.links"
        resource-label="activity logs"
    />

    <ConfirmDialog
        :open="confirmingDeleteId !== null"
        title="Delete activity log"
        description="This log entry will be permanently deleted. This cannot be undone."
        @confirm="performDelete"
        @update:open="
            (open) => {
                if (!open) confirmingDeleteId = null;
            }
        "
    />

    <ConfirmDialog
        :open="confirmingBulkDelete"
        title="Delete selected activity logs"
        description="These log entries will be permanently deleted. This cannot be undone."
        @confirm="performBulkDelete"
        @update:open="(open) => (confirmingBulkDelete = open)"
    />
</template>
```
