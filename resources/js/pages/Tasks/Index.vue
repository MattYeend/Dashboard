<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    index as tasksIndex,
    create as tasksCreate,
    show as tasksShow,
    edit as tasksEdit,
    destroy as tasksDestroy,
} from '@/routes/tasks';
import type { Task, Pagination, PermissionsMeta } from '@/types';

interface Props {
    tasks: {
        data: Task[];
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
    router.get(tasksIndex.url(), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function destroy(id: number): void {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(tasksDestroy.url(id));
    }
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="items-centre mb-4 flex justify-between">
                <h1 class="text-grey-900 text-2xl font-semibold">Tasks</h1>
                <Link
                    v-if="permissions_meta.can_create"
                    :href="tasksCreate.url()"
                    class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
                >
                    Add Task
                </Link>
            </div>

            <div class="mb-4 flex flex-wrap gap-2">
                <input
                    v-model="filters.search"
                    type="text"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    placeholder="Search tasks…"
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
                                Status
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Assigned To
                            </th>
                            <th
                                class="text-grey-500 px-6 py-3 text-left text-xs font-medium tracking-wide uppercase"
                            >
                                Due Date
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-grey-200 divide-y">
                        <tr v-if="!tasks.data?.length">
                            <td
                                colspan="5"
                                class="text-centre text-grey-500 px-6 py-4 text-sm"
                            >
                                No tasks found.
                            </td>
                        </tr>
                        <tr v-for="task in tasks.data ?? []" :key="task.id">
                            <td
                                class="text-grey-900 px-6 py-4 text-sm font-medium whitespace-nowrap"
                            >
                                {{ task.title }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                <span
                                    v-if="task.status"
                                    :style="{
                                        backgroundColor:
                                            task.status.background_colour ??
                                            '#e2e8f0',
                                        color:
                                            task.status.text_colour ??
                                            '#1a202c',
                                    }"
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ task.status.title }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ task.assignee?.name ?? '—' }}
                            </td>
                            <td
                                class="text-grey-500 px-6 py-4 text-sm whitespace-nowrap"
                            >
                                {{ formatDate(task.due_date) }}
                            </td>
                            <td
                                class="space-x-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                            >
                                <Link
                                    :href="tasksShow.url(task.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="tasksEdit.url(task.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:text-red-900"
                                    @click="destroy(task.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="tasks.meta.last_page > 1"
                class="mt-4 flex items-center justify-between"
            >
                <p class="text-grey-500 text-sm">
                    Showing {{ tasks.meta.from ?? 0 }} to
                    {{ tasks.meta.to ?? 0 }} of {{ tasks.meta.total }} tasks
                </p>
                <div class="flex gap-x-1">
                    <Link
                        v-for="link in tasks.links"
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
