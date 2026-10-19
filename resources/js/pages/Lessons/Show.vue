<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import LessonAuditDetails from '@/pages/Lessons/components/LessonAuditDetails.vue';
import LessonBasicDetails from '@/pages/Lessons/components/LessonBasicDetails.vue';
import LessonContentDetails from '@/pages/Lessons/components/LessonContentDetails.vue';
import LessonNavigation from '@/pages/Lessons/components/LessonNavigation.vue';
import LessonSettingsDetails from '@/pages/Lessons/components/LessonSettingsDetails.vue';
import { edit, index as lessonsIndex } from '@/routes/lessons';
import type { Lesson, LessonNeighbour } from '@/types';
 
const props = defineProps<{
    lesson: Lesson;
    navigation: { previous: LessonNeighbour | null; next: LessonNeighbour | null };
}>();
 
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Lessons', href: '/lessons' },
            { title: 'Details', href: '#' },
        ],
    },
});
</script>
 
<template>
    <Head :title="lesson.title" />
 
    <div class="mx-auto grid max-w-3xl gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl text-gray-300">{{ lesson.title }}</h1>
            <div class="flex gap-2">
                <Button as-child variant="outline">
                    <Link :href="lessonsIndex().url">Back</Link>
                </Button>
                <Button as-child>
                    <Link :href="edit(props.lesson.id).url">Edit</Link>
                </Button>
            </div>
        </div>
 
        <LessonBasicDetails :lesson="lesson" />
        <LessonContentDetails :lesson="lesson" />
        <LessonSettingsDetails :lesson="lesson" />
        <LessonAuditDetails :lesson="lesson" />
        <LessonNavigation :previous="navigation.previous" :next="navigation.next" />
    </div>
</template>