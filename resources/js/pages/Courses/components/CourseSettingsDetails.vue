<script setup lang="ts">
import { computed } from 'vue';
import type { Course } from '@/types';

const props = defineProps<{
    course: Course;
}>();

const difficulty = computed<string>(
    () => props.course.difficulty_label ?? 'Not set',
);

const duration = computed<string>(() =>
    props.course.estimated_duration_minutes
        ? `${props.course.estimated_duration_minutes} minutes`
        : 'Not set',
);

const instructorName = computed<string>(
    () => props.course.instructor?.name ?? 'Unassigned',
);

const published = computed<string>(() =>
    props.course.published_at
        ? new Date(props.course.published_at).toLocaleDateString('en-GB')
        : 'Draft',
);
</script>

<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-4">
        <div>
            <dt class="text-xs text-gray-400">Difficulty</dt>
            <dd class="text-sm text-gray-300">{{ difficulty }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Duration</dt>
            <dd class="text-sm text-gray-300">{{ duration }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Instructor</dt>
            <dd class="text-sm text-gray-300">{{ instructorName }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Published</dt>
            <dd class="text-sm text-gray-300">{{ published }}</dd>
        </div>
    </dl>
</template>
