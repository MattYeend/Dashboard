<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { Task, Pagination, PermissionsMeta } from '@/types';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    tasks: Task[];
    pagination: Pagination;
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}>();

const selectedIds = ref<number[]>([]);

const filters = ref({
    search: '',
    trashed: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
});

const allSelected = computed(
    () =>
        props.tasks.length > 0 &&
        selectedIds.value.length === props.tasks.length,
);

function toggleSelectAll(): void {
    selectedIds.value = allSelected.value ? [] : props.tasks.map((t) => t.id);
}

function applyFilters(): void {
    router.get(route('tasks.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
}

function goToPage(page: number): void {
    router.get(
        route('tasks.index'),
        { ...filters.value, page },
        {
            preserveState: true,
        },
    );
}

function confirmDelete(id: number): void {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }

    router.delete(route('tasks.destroy', id));
}

function bulkDelete(): void {
    if (!confirm(`Delete ${selectedIds.value.length} selected tasks?`)) {
        return;
    }

    router.post(
        route('tasks.bulk.delete'),
        { ids: selectedIds.value },
        {
            onSuccess: () => {
                selectedIds.value = [];
            },
        },
    );
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
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">Tasks</h1>
                <Link
                    v-if="permissions_meta.can_create"
                    :href="route('tasks.create')"
                    class="btn btn-primary btn-sm"
                >
                    Create Task
                </Link>
            </div>
        </template>

        <div class="container-fluid py-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input
                                v-model="filters.search"
                                type="text"
                                class="form-control"
                                placeholder="Search tasks…"
                                @input="applyFilters"
                            />
                        </div>
                        <div class="col-md-3">
                            <select
                                v-model="filters.trashed"
                                class="form-select"
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
                        </div>
                        <div class="col-md-3">
                            <select
                                v-model="filters.sort_by"
                                class="form-select"
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
                        </div>
                        <div class="col-md-2">
                            <select
                                v-model="filters.sort_direction"
                                class="form-select"
                                @change="applyFilters"
                            >
                                <option value="asc">Ascending</option>
                                <option value="desc">Descending</option>
                            </select>
                        </div>
                    </div>

                    <div
                        v-if="selectedIds.length > 0"
                        class="d-flex mb-3 gap-2"
                    >
                        <button
                            class="btn btn-danger btn-sm"
                            @click="bulkDelete"
                        >
                            Delete Selected ({{ selectedIds.length }})
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table-hover table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 2.5rem">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            :checked="allSelected"
                                            @change="toggleSelectAll"
                                        />
                                    </th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="tasks.length === 0">
                                    <td
                                        colspan="6"
                                        class="py-4 text-center text-muted"
                                    >
                                        No tasks found.
                                    </td>
                                </tr>
                                <tr v-for="task in tasks" :key="task.id">
                                    <td>
                                        <input
                                            v-model="selectedIds"
                                            type="checkbox"
                                            class="form-check-input"
                                            :value="task.id"
                                        />
                                    </td>
                                    <td>{{ task.title }}</td>
                                    <td>
                                        <span
                                            v-if="task.status"
                                            class="badge"
                                            :style="{
                                                backgroundColor:
                                                    task.status
                                                        .background_colour ??
                                                    '#e2e8f0',
                                                color:
                                                    task.status.text_colour ??
                                                    '#1a202c',
                                            }"
                                        >
                                            {{ task.status.title }}
                                        </span>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                    <td>{{ task.assignee?.name ?? '—' }}</td>
                                    <td>{{ formatDate(task.due_date) }}</td>
                                    <td class="text-end">
                                        <div
                                            class="d-flex justify-content-end gap-1"
                                        >
                                            <Link
                                                :href="
                                                    route('tasks.show', task.id)
                                                "
                                                class="btn btn-outline-secondary btn-sm"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="
                                                    route('tasks.edit', task.id)
                                                "
                                                class="btn btn-outline-primary btn-sm"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                class="btn btn-outline-danger btn-sm"
                                                @click="confirmDelete(task.id)"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav
                        v-if="pagination.last_page > 1"
                        class="d-flex justify-content-between align-items-center mt-3"
                    >
                        <p class="small mb-0 text-muted">
                            Showing {{ pagination.from }}-{{ pagination.to }} of
                            {{ pagination.total }} tasks
                        </p>
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                v-for="page in pagination.last_page"
                                :key="page"
                                class="page-item"
                                :class="{
                                    active: page === pagination.current_page,
                                }"
                            >
                                <button
                                    class="page-link"
                                    @click="goToPage(page)"
                                >
                                    {{ page }}
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
