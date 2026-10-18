<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import CourseAuditDetails from '@/pages/Courses/components/CourseAuditDetails.vue';
import CourseBasicDetails from '@/pages/Courses/components/CourseBasicDetails.vue';
import CourseSettingsDetails from '@/pages/Courses/components/CourseSettingsDetails.vue';
import {
    destroy as coursesDestroy,
    edit,
    index as coursesIndex,
} from '@/routes/courses';
import type { Course } from '@/types';

const props = defineProps<{
    course: Course;
}>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

/**
 * Move the course to trash, then close the confirmation dialog.
 */
function destroy(): void {
    deleteProcessing.value = true;

    router.delete(coursesDestroy.url(props.course.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: coursesIndex().url },
            { title: 'Details', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="course.title" />

    <div class="mx-auto grid max-w-3xl gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl text-gray-300">{{ course.title }}</h1>
            <div class="flex gap-2">
                <Button as-child variant="outline">
                    <Link :href="coursesIndex().url">Back</Link>
                </Button>
                <Button as-child>
                    <Link :href="edit(props.course.id).url">Edit</Link>
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    class="text-red-600"
                    @click="deleteDialogOpen = true"
                >
                    Delete
                </Button>
            </div>
        </div>

        <CourseBasicDetails :course="course" />
        <CourseSettingsDetails :course="course" />
        <CourseAuditDetails :course="course" />
    </div>

    <ConfirmDialog
        v-model:open="deleteDialogOpen"
        title="Delete course"
        description="This course will be moved to trash."
        confirm-label="Delete"
        :processing="deleteProcessing"
        @confirm="destroy"
    />
</template>
