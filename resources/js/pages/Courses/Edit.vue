<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import CourseForm from '@/pages/Courses/components/CourseForm.vue';
import { index as coursesIndex, show, update } from '@/routes/courses';
import type { Course, DifficultyOption, UserOption } from '@/types';

const props = defineProps<{
    course: Course;
    difficulties: DifficultyOption[];
    instructors: UserOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: coursesIndex().url },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    title: props.course.title,
    summary: props.course.summary ?? '',
    description: props.course.description ?? '',
    difficulty: props.course.difficulty as string,
    estimated_duration_minutes: props.course.estimated_duration_minutes as number | null,
    instructor_id: props.course.instructor_id as number | null,
    published_at: props.course.published_at?.slice(0, 16) ?? '',
});

/**
 * Submit the form, converting blank optional fields to null.
 */
function submit(): void {
    form
        .transform((data) => ({
            ...data,
            summary: nullIfBlank(data.summary),
            description: nullIfBlank(data.description),
            estimated_duration_minutes: numberOrNull(data.estimated_duration_minutes),
            instructor_id: numberOrNull(data.instructor_id),
            published_at: nullIfBlank(data.published_at),
        }))
        .put(update(props.course.id).url);
}
</script>

<template>
    <Head title="Edit course" />

    <form class="mx-auto grid max-w-3xl gap-6 p-4" @submit.prevent="submit">
        <CourseForm
            v-model:title="form.title"
            v-model:summary="form.summary"
            v-model:description="form.description"
            v-model:difficulty="form.difficulty"
            v-model:estimated-duration-minutes="form.estimated_duration_minutes"
            v-model:instructor-id="form.instructor_id"
            v-model:published-at="form.published_at"
            :errors="form.errors"
            :difficulties="difficulties"
            :instructors="instructors"
        />

        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing">Save changes</Button>
            <Button as-child variant="outline">
                <Link :href="show(course.id).url">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
