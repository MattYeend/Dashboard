<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import LessonAuditDetails from '@/pages/Lessons/components/LessonAuditDetails.vue';
import LessonBasicDetails from '@/pages/Lessons/components/LessonBasicDetails.vue';
import LessonContentDetails from '@/pages/Lessons/components/LessonContentDetails.vue';
import LessonNavigation from '@/pages/Lessons/components/LessonNavigation.vue';
import LessonSettingsDetails from '@/pages/Lessons/components/LessonSettingsDetails.vue';
import {
    destroy as lessonsDestroy,
    edit,
    index as lessonsIndex,
} from '@/routes/lessons';
import type { Lesson, LessonNeighbour } from '@/types';

interface Props {
    lesson: Lesson;
    navigation: {
        previous: LessonNeighbour | null;
        next: LessonNeighbour | null;
    };
    canEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    canEdit: true,
});

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.lesson?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

/**
 * Move the lesson to trash, then close the confirmation dialog.
 */
function destroy(): void {
    if (!props.lesson?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(lessonsDestroy.url(props.lesson.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Lessons', href: lessonsIndex().url },
            { title: 'Details', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="lesson.title" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ lesson.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="lessonsIndex().url">Back</Link>
                    </Button>
                    <Button v-if="canEdit" as-child>
                        <Link :href="edit(props.lesson.id).url">Edit</Link>
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
                <LessonBasicDetails :lesson="lesson" />
                <LessonContentDetails :lesson="lesson" />
                <LessonSettingsDetails :lesson="lesson" />
                <LessonAuditDetails :lesson="lesson" />
                <LessonNavigation
                    :previous="navigation.previous"
                    :next="navigation.next"
                />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete lesson"
            description="This lesson will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
