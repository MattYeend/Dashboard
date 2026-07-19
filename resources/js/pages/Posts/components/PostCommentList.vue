<script setup lang="ts">
import PostCommentItem from '@/pages/Posts/components/PostCommentItem.vue';
import type { Comment } from '@/types';

interface Props {
    postId: number;
    comments: Comment[];
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-3">
        <p v-if="comments.length === 0" class="text-sm text-gray-400">
            No comments yet.
        </p>
        <PostCommentItem
            v-for="comment in comments"
            :key="comment.id"
            :post-id="postId"
            :comment="comment"
        />
    </div>
</template>
