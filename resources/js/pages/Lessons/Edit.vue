<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import LessonForm from '@/pages/Lessons/components/LessonForm.vue';
import { index as lessonsIndex, show, update } from '@/routes/lessons';
import type { CourseOption, Lesson } from '@/types';

const props = defineProps<{
    lesson: Lesson;
    course_options: CourseOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Lessons', href: lessonsIndex().url },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    course_id: props.lesson.course_id as number | null,
    title: props.lesson.title,
    summary: props.lesson.summary ?? '',
    content: props.lesson.content ?? '',
    video_url: props.lesson.video_url ?? '',
    duration_minutes: props.lesson.duration_minutes as number | null,
    is_preview: props.lesson.is_preview,
    published_at: props.lesson.published_at?.slice(0, 16) ?? '',
});

/**
 * Submit the form, converting blank optional fields to null.
 */
function submit(): void {
    form.transform((data) => ({
        ...data,
        course_id: numberOrNull(data.course_id),
        summary: nullIfBlank(data.summary),
        content: nullIfBlank(data.content),
        video_url: nullIfBlank(data.video_url),
        duration_minutes: numberOrNull(data.duration_minutes),
        published_at: nullIfBlank(data.published_at),
    })).put(update(props.lesson.id).url);
}
</script>

<template>
    <Head title="Edit lesson" />

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
            :courses="course_options"
        />

        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing"
                >Save changes</Button
            >
            <Button as-child variant="outline">
                <Link :href="show(lesson.id).url">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
