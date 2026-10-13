<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import CourseForm from '@/pages/Courses/components/CourseForm.vue';
import { index as coursesIndex, store } from '@/routes/courses';
import type { DifficultyOption, UserOption } from '@/types';
 
defineProps<{
    difficulties: DifficultyOption[];
    instructors: UserOption[];
}>();
 
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: coursesIndex().url },
            { title: 'Create', href: '#' },
        ],
    },
});
 
const form = useForm({
    title: '',
    summary: '',
    description: '',
    difficulty: 'beginner',
    estimated_duration_minutes: null as number | null,
    instructor_id: null as number | null,
    published_at: '',
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
        .post(store().url);
}
</script>
 
<template>
    <Head title="Create course" />
 
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
            <Button type="submit" :disabled="form.processing">Create course</Button>
            <Button as-child variant="outline">
                <Link :href="coursesIndex().url">Cancel</Link>
            </Button>
        </div>
    </form>
</template>