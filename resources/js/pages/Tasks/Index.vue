<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { Task, Pagination, PermissionsMeta } from '@/types';
import TaskRow from './components/TaskRow.vue';

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
        { preserveState: true },
    );
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
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Tasks</h1>
            <Link
                v-if="permissions_meta.can_create"
                :href="route('tasks.create')"
                class="btn btn-primary"
            >
                Add Task
            </Link>
        </div>

        <div class="mb-3 flex flex-wrap gap-2">
            <input
                v-model="filters.search"
                type="text"
                class="form-control w-auto"
                placeholder="Search tasks…"
                @input="applyFilters"
            />
            <select
                v-model="filters.trashed"
                class="form-select w-auto"
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
                class="form-select w-auto"
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
                class="form-select w-auto"
                @change="applyFilters"
            >
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </div>

        <div v-if="selectedIds.length > 0" class="mb-3">
            <button class="btn btn-danger btn-sm" @click="bulkDelete">
                Delete Selected ({{ selectedIds.length }})
            </button>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="p-2 text-left" style="width: 2.5rem">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            :checked="allSelected"
                            @change="toggleSelectAll"
                        />
                    </th>
                    <th class="p-2 text-left">Title</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Assigned To</th>
                    <th class="p-2 text-left">Due Date</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="tasks.length === 0">
                    <td colspan="6" class="p-4 text-center text-muted">
                        No tasks found.
                    </td>
                </tr>
                <TaskRow
                    v-for="task in tasks"
                    :key="task.id"
                    :task="task"
                    :selected-ids="selectedIds"
                    @toggle-select="
                        (id) => {
                            const idx = selectedIds.indexOf(id);
                            idx === -1
                                ? selectedIds.push(id)
                                : selectedIds.splice(idx, 1);
                        }
                    "
                />
            </tbody>
        </table>

        <nav
            v-if="pagination.last_page > 1"
            class="mt-3 flex items-center justify-between"
        >
            <p class="text-sm text-muted">
                Showing {{ pagination.from }}–{{ pagination.to }} of
                {{ pagination.total }} tasks
            </p>
            <ul class="pagination pagination-sm mb-0">
                <li
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="page-item"
                    :class="{ active: page === pagination.current_page }"
                >
                    <button class="page-link" @click="goToPage(page)">
                        {{ page }}
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>
