<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { courseLessons } from '@/routes/quizzes';
import type { CourseOption, LessonOption } from '@/types';

defineProps<{
    errors: Partial<Record<string, string>>;
    courses: CourseOption[];
}>();

const courseId = defineModel<number | null>('courseId', { required: true });
const lessonId = defineModel<number | null>('lessonId', { required: true });
const title = defineModel<string>('title', { required: true });
const description = defineModel<string>('description', { required: true });

const lessons = ref<LessonOption[]>([]);

/**
 * Load the lessons for the selected course.
 */
async function loadLessons(id: number | null): Promise<void> {
    if (id === null) {
        lessons.value = [];

        return;
    }

    const { data } = await axios.get<LessonOption[]>(courseLessons().url, {
        params: { course_id: id },
    });

    lessons.value = data;
}

watch(
    courseId,
    async (id, previous) => {
        if (previous !== undefined && previous !== id) {
            lessonId.value = null;
        }

        await loadLessons(id);
    },
    { immediate: true },
);
</script>

<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4">
        <div class="grid gap-2 sm:grid-cols-2 sm:gap-4">
            <div class="grid gap-2">
                <Label for="course">Course</Label>
                <Select v-model="courseId">
                    <SelectTrigger id="course">
                        <SelectValue placeholder="Select course" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="course in courses"
                            :key="course.id"
                            :value="course.id"
                        >
                            {{ course.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.course_id" />
            </div>

            <div class="grid gap-2">
                <Label for="lesson">Lesson (optional)</Label>
                <Select v-model="lessonId" :disabled="lessons.length === 0">
                    <SelectTrigger id="lesson">
                        <SelectValue placeholder="Whole course" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="lesson in lessons"
                            :key="lesson.id"
                            :value="lesson.id"
                        >
                            {{ lesson.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.lesson_id" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input id="title" v-model="title" maxlength="255" required />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="description" rows="4" />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>