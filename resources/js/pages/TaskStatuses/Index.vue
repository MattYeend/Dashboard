<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    index as taskStatusesIndex,
    create as taskStatusesCreate,
    show as taskStatusesShow,
    edit as taskStatusesEdit,
    destroy as taskStatusesDestroy,
} from '@/routes/task-statuses';
import type { TaskStatus, Pagination, PermissionsMeta } from '@/types';

interface Props {
    taskStatuses: {
        data: TaskStatus[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: Pagination;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

defineProps<Props>();

const filters = ref({
    search: '',
    trashed: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
});

function applyFilters(): void {
    router.get(taskStatusesIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this task status?')) {
        router.delete(taskStatusesDestroy.url(id));
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="items-centre mb-4 flex justify-between">
                <h1 class="text-grey-900 text-2xl font-semibold">
                    Task Statuses
                </h1>
                <Link
                    v-if="permissions_meta.can_create"
                    :href="taskStatusesCreate.url()"
                    class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
                >
                    Add Task Status
                </Link>
            </div>

            <div class="mb-4 flex flex-wrap gap-2">
                <input
                    v-model="filters.search"
                    type="text"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    placeholder="Search task statuses…"
                    @input="applyFilters"
                />
                <select
                    v-model="filters.trashed"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    @change="applyFilters"
                >
                    <option
                        v-for="(label, value) in trash_filters"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
                <select
                    v-model="filters.sort_by"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    @change="applyFilters"
                >
                    <option
                        v-for="(label, value) in sort_fields"
                        :key="value"
                        :value="value"
                    >
                        Sort by {{ label }}
                    </option>
                </select>
                <select
                    v-model="filters.sort_direction"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    @change="applyFilters"
                >
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
            </div>

            <div
                class="ring-opacity-5 overflow-hidden shadow ring-1 ring-black sm:rounded-lg"
            >
                <table class="divide-grey-300 min-w-full divide-y">
                    <thead>
                        <tr>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Title
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Description
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Preview
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-grey-200 divide-y">
                        <tr v-if="!taskStatuses.data?.length">
                            <td
                                colspan="4"
                                class="text-centre text-grey-500 px-6 py-4 text-sm"
                            >
                                No task statuses found.
                            </td>
                        </tr>
                        <tr
                            v-for="status in taskStatuses.data ?? []"
                            :key="status.id"
                        >
                            <td
                                class="text-grey-900 px-6 py-4 text-sm font-medium whitespace-nowrap"
                            >
                                {{ status.title }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ status.description ?? '—' }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :style="{
                                        backgroundColor:
                                            status.background_colour,
                                        color: status.text_colour,
                                    }"
                                >
                                    {{ status.title }}
                                </span>
                            </td>
                            <td
                                class="space-x-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                            >
                                <Link :href="taskStatusesShow.url(status.id)">
                                    View
                                </Link>
                                <Link :href="taskStatusesEdit.url(status.id)">
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:text-red-900"
                                    @click="destroy(status.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="taskStatuses.meta.last_page > 1"
                class="mt-4 flex items-center justify-between"
            >
                <p class="text-grey-500 text-sm">
                    Showing {{ taskStatuses.meta.from ?? 0 }} to
                    {{ taskStatuses.meta.to ?? 0 }} of
                    {{ taskStatuses.meta.total }} task statuses
                </p>
                <div class="flex gap-x-1">
                    <Link
                        v-for="link in taskStatuses.links"
                        :key="link.label"
                        :href="link.url ?? ''"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.url === null
                                ? 'pointer-events-none opacity-40'
                                : 'hover:bg-accent',
                            link.active ? 'font-semibold' : '',
                        ]"
                        preserve-scroll
                    >
                        <span v-html="link.label" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
