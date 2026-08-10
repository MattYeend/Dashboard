<script setup lang="ts">
import CommentsSection from '@/components/Comments/CommentsSection.vue';
import type { Post } from '@/types';

interface Props {
    post: Post;
}

defineProps<Props>();
</script>

<template>
    <CommentsSection
        commentable-type="post"
        :commentable-id="post.id"
        :comments="post.comments ?? []"
        :comments-count="post.comments_count"
    />
</template>
