<script setup lang="ts">
import CommentItem from '@/components/Comments/CommentItem.vue';
import type { Comment } from '@/types';

interface Props {
    comments: Comment[];
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-3">
        <p v-if="comments.length === 0" class="text-sm text-gray-400">
            No comments yet.
        </p>
        <CommentItem
            v-for="comment in comments"
            :key="comment.id"
            :comment="comment"
        />
    </div>
</template>
