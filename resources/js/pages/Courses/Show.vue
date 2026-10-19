<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    destroy as coursesDestroy,
    edit,
    index as coursesIndex,
} from '@/routes/courses';
import type { Course } from '@/types';
import CourseAuditDetails from './components/CourseAuditDetails.vue';
import CourseBasicDetails from './components/CourseBasicDetails.vue';
import CourseLessonList from './components/CourseLessonList.vue';
import CourseSettingsDetails from './components/CourseSettingsDetails.vue';

interface Props {
    course: Course;
    canEdit: boolean;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.course?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

/**
 * Move the course to trash, then close the confirmation dialog.
 */
function destroy(): void {
    if (!props.course?.id) {
        return;
    }

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
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ course.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="coursesIndex().url">Back</Link>
                    </Button>
                    <Button v-if="canEdit" as-child>
                        <Link :href="edit(props.course.id).url">Edit</Link>
                    </Button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <CourseBasicDetails :course="course" />
                <CourseSettingsDetails :course="course" />
                <CourseLessonList
                    :course-id="course.id"
                    :lessons="course.lessons ?? []"
                    :can-edit="canEdit"
                />
                <CourseAuditDetails :course="course" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete course"
            description="This course will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
