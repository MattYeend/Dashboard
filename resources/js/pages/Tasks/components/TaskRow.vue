<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import type { Task } from '@/types';

defineProps<{
    task: Task;
    selectedIds: number[];
}>();

const emit = defineEmits<{
    (e: 'toggle-select', id: number): void;
}>();

function confirmDelete(id: number): void {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }

    router.delete(route('tasks.destroy', id));
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
    <tr>
        <td class="p-2">
            <input
                type="checkbox"
                class="form-check-input"
                :checked="selectedIds.includes(task.id)"
                @change="emit('toggle-select', task.id)"
            />
        </td>
        <td class="p-2">{{ task.title }}</td>
        <td class="p-2">
            <span
                v-if="task.status"
                class="badge"
                :style="{
                    backgroundColor: task.status.background_colour ?? '#e2e8f0',
                    color: task.status.text_colour ?? '#1a202c',
                }"
            >
                {{ task.status.title }}
            </span>
            <span v-else class="text-muted">—</span>
        </td>
        <td class="p-2">{{ task.assignee?.name ?? '—' }}</td>
        <td class="p-2">{{ formatDate(task.due_date) }}</td>
        <td class="p-2">
            <div class="flex gap-1">
                <Link
                    :href="route('tasks.show', task.id)"
                    class="btn btn-outline-secondary btn-sm"
                >
                    View
                </Link>
                <Link
                    :href="route('tasks.edit', task.id)"
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
</template>
