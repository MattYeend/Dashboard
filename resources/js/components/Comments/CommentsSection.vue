<script setup lang="ts">
import CommentForm from '@/components/Comments/CommentForm.vue';
import CommentList from '@/components/Comments/CommentList.vue';
import type { Comment } from '@/types';

interface Props {
    commentableType: string;
    commentableId: number;
    comments: Comment[];
    commentsCount?: number;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Comments ({{ commentsCount ?? comments.length }})
            </h3>
        </div>
        <div class="border-t border-gray-500 px-4 py-4 sm:px-6">
            <CommentForm
                :commentable-type="commentableType"
                :commentable-id="commentableId"
            />
            <div class="mt-4">
                <CommentList :comments="comments" />
            </div>
        </div>
    </div>
</template>
