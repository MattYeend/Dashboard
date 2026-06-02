<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import type { TaskStatus } from '@/types'

const props = defineProps<{ status: TaskStatus }>()

const deleteForm = useForm({})

function destroy() {
    if (!confirm('Delete this task status?')) return
    deleteForm.delete(route('task-statuses.destroy', props.status.id))
}
</script>

<template>
    <tr>
        <td class="p-2">{{ status.title }}</td>
        <td class="p-2">{{ status.description ?? '—' }}</td>
        <td class="p-2">
        <span
            class="px-2 py-1 rounded text-xs font-medium"
            :style="{ backgroundColor: status.background_colour, color: status.text_colour }"
        >
            {{ status.title }}
        </span>
        </td>
        <td class="p-2 space-x-2">
            <Link :href="route('task-statuses.show', status.id)">View</Link>
            <Link :href="route('task-statuses.edit', status.id)">Edit</Link>
            <button type="button" @click="destroy">Delete</button>
        </td>
    </tr>
</template>