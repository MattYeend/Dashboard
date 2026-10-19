<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/lessons';
import type { LessonNeighbour } from '@/types';
 
defineProps<{
    previous: LessonNeighbour | null;
    next: LessonNeighbour | null;
}>();
</script>
 
<template>
    <nav class="flex justify-between text-sm text-gray-300" aria-label="Lesson navigation">
        <Link v-if="previous" :href="show(previous.id).url">Previous: {{ previous.title }}</Link>
        <span v-else />
        <Link v-if="next" :href="show(next.id).url">Next: {{ next.title }}</Link>
    </nav>
</template>