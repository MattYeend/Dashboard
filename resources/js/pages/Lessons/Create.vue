<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import LessonForm from '@/pages/Lessons/components/LessonForm.vue';
import { index as lessonsIndex, store } from '@/routes/lessons';
import type { CourseOption } from '@/types';
 
const props = defineProps<{
    courses: CourseOption[];
    selectedCourseId: number | null;
}>();
 
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Lessons', href: lessonsIndex().url },
            { title: 'Create', href: '#' },
        ],
    },
});
 
const form = useForm({
    course_id: props.selectedCourseId,
    title: '',
    summary: '',
    content: '',
    video_url: '',
    duration_minutes: null as number | null,
    is_preview: false,
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
            content: nullIfBlank(data.content),
            video_url: nullIfBlank(data.video_url),
            duration_minutes: numberOrNull(data.duration_minutes),
            published_at: nullIfBlank(data.published_at),
        }))
        .post(store().url);
}
</script>
 
<template>
    <Head title="Create lesson" />
 
    <form class="mx-auto grid max-w-3xl gap-6 p-4" @submit.prevent="submit">
        <LessonForm
            v-model:course-id="form.course_id"
            v-model:title="form.title"
            v-model:summary="form.summary"
            v-model:content="form.content"
            v-model:video-url="form.video_url"
            v-model:duration-minutes="form.duration_minutes"
            v-model:is-preview="form.is_preview"
            v-model:published-at="form.published_at"
            :errors="form.errors"
            :courses="courses"
        />
 
        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing">Create lesson</Button>
            <Button as-child variant="outline">
                <Link :href="lessonsIndex().url">Cancel</Link>
            </Button>
        </div>
    </form>
</template>