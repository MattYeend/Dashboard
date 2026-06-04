<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import TaskAssignmentDetails from '@/pages/Tasks/components/TaskAssignmentDetails.vue';
import TaskBasicDetails from '@/pages/Tasks/components/TaskBasicDetails.vue';
import TaskDateDetails from '@/pages/Tasks/components/TaskDateDetails.vue';
import type { Task, PermissionsMeta } from '@/types';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineProps<{
    task: Task;
    permissions_meta: PermissionsMeta;
}>();
</script>

<template>
    <Head :title="`Task - ${task.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">{{ task.title }}</h1>
                <div class="d-flex gap-2">
                    <Link
                        v-if="permissions_meta.can_create"
                        :href="route('tasks.edit', task.id)"
                        class="btn btn-primary btn-sm"
                    >
                        Edit
                    </Link>
                    <Link :href="route('tasks.index')" class="btn btn-secondary btn-sm">
                        Back to Tasks
                    </Link>
                </div>
            </div>
        </template>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-8">
                    <TaskBasicDetails :task="task" />
                    <TaskAssignmentDetails :task="task" />
                    <TaskDateDetails :task="task" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>