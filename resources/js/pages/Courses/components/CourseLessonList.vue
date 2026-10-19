<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { create, reorder, show } from '@/routes/lessons';
import type { Lesson } from '@/types';

const props = defineProps<{
    courseId: number;
    lessons: Lesson[];
    canEdit: boolean;
}>();

const ordered = ref<Lesson[]>([...props.lessons]);
const saving = ref(false);

watch(
    () => props.lessons,
    (value) => {
        ordered.value = [...value];
    },
);

const hasLessons = computed(() => ordered.value.length > 0);

/**
 * Move a lesson up or down and save the new order.
 */
function move(index: number, direction: -1 | 1): void {
    const target = index + direction;

    if (target < 0 || target >= ordered.value.length) {
        return;
    }

    const next = [...ordered.value];
    [next[index], next[target]] = [next[target], next[index]];
    ordered.value = next;
    saving.value = true;

    router.post(
        reorder().url,
        {
            course_id: props.courseId,
            lesson_ids: next.map((lesson) => lesson.id),
        },
        { preserveScroll: true, onFinish: () => (saving.value = false) },
    );
}
</script>

<template>
    <section class="grid gap-3 border-b border-gray-500 pb-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg text-gray-300">Lessons</h2>
            <Button v-if="canEdit" as-child size="sm">
                <Link :href="create({ query: { course_id: courseId } }).url"
                    >Add lesson</Link
                >
            </Button>
        </div>

        <p v-if="!hasLessons" class="text-sm text-gray-400">No lessons yet.</p>

        <ol v-else class="grid gap-2">
            <li
                v-for="(lesson, index) in ordered"
                :key="lesson.id"
                class="flex items-center justify-between border-b border-gray-500 pb-2"
            >
                <Link :href="show(lesson.id).url" class="text-sm text-gray-300">
                    {{ index + 1 }}. {{ lesson.title }}
                </Link>
                <div v-if="canEdit" class="flex gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        :disabled="saving || index === 0"
                        :aria-label="`Move ${lesson.title} up`"
                        @click="move(index, -1)"
                    >
                        Up
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        :disabled="saving || index === ordered.length - 1"
                        :aria-label="`Move ${lesson.title} down`"
                        @click="move(index, 1)"
                    >
                        Down
                    </Button>
                </div>
            </li>
        </ol>
    </section>
</template>
