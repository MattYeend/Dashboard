<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import CourseAuditDetails from '@/pages/Courses/components/CourseAuditDetails.vue';
import CourseBasicDetails from '@/pages/Courses/components/CourseBasicDetails.vue';
import CourseSettingsDetails from '@/pages/Courses/components/CourseSettingsDetails.vue';
import { edit, index as coursesIndex } from '@/routes/courses';
import type { Course } from '@/types';
 
const props = defineProps<{
    course: Course;
}>();
 
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
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
            </div>
        </div>
 
        <CourseBasicDetails :course="course" />
        <CourseSettingsDetails :course="course" />
        <CourseAuditDetails :course="course" />
    </div>
</template>