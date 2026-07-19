<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { Comment } from '@/types';

interface Props {
    postId: number;
    comment: Comment;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(`/posts/${props.postId}/comments/${props.comment.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="border-b border-gray-700 pb-3">
        <p class="text-sm text-gray-300">{{ comment.content }}</p>
        <div class="mt-1 flex items-center gap-2 text-xs text-gray-400">
            <span>{{ comment.creator?.name ?? '—' }}</span>
            <button
                v-if="comment.can_delete"
                type="button"
                class="text-red-600"
                @click="requestDestroy"
            >
                Delete
            </button>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete comment"
            description="This comment will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>