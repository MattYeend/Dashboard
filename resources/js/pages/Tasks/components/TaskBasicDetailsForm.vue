<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import type { TaskStatus } from '@/types';

interface TaskFormData {
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
}

interface Props {
    statuses: TaskStatus[];
    errors: Partial<InertiaFormProps<TaskFormData>['errors']>;
}

defineProps<Props>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="title" class="text-gray-700 block text-sm font-medium">
                Title <span class="text-red-600">*</span>
            </label>
            <input
                id="title"
                v-model="title"
                type="text"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                placeholder="Enter task title"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                {{ errors.title }}
            </p>
        </div>

        <div>
            <label
                for="description"
                class="text-gray-700 block text-sm font-medium"
            >
                Description
            </label>
            <textarea
                id="description"
                :value="description ?? ''"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                rows="4"
                placeholder="Enter task description"
                @input="
                    description =
                        ($event.target as HTMLTextAreaElement).value || null
                "
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
            </p>
        </div>

        <div>
            <label
                for="status_id"
                class="text-gray-700 block text-sm font-medium"
            >
                Status
            </label>
            <select
                id="status_id"
                :value="statusId"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="
                    statusId = ($event.target as HTMLSelectElement).value
                        ? Number(($event.target as HTMLSelectElement).value)
                        : null
                "
            >
                <option :value="null">-- Select a status --</option>
                <option
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.title }}
                </option>
            </select>
            <p v-if="errors.status_id" class="mt-1 text-sm text-red-600">
                {{ errors.status_id }}
            </p>
        </div>
    </div>
</template>
