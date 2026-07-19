<script setup lang="ts">
import PostCommentForm from '@/pages/Posts/components/PostCommentForm.vue';
import PostCommentList from '@/pages/Posts/components/PostCommentList.vue';
import type { Post } from '@/types';

interface Props {
    post: Post;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Comments ({{ post.comments_count ?? 0 }})
            </h3>
        </div>
        <div class="border-t border-gray-500 px-4 py-4 sm:px-6">
            <PostCommentForm :post-id="post.id" />
            <div class="mt-4">
                <PostCommentList
                    :post-id="post.id"
                    :comments="post.comments ?? []"
                />
            </div>
        </div>
    </div>
</template>
