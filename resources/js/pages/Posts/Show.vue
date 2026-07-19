<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PostAuditDetails from '@/pages/Posts/components/PostAuditDetails.vue';
import PostBasicDetails from '@/pages/Posts/components/PostBasicDetails.vue';
import PostCategoriesDetails from '@/pages/Posts/components/PostCategoriesDetails.vue';
import PostComments from '@/pages/Posts/components/PostComments.vue';
import PostLikeButton from '@/pages/Posts/components/PostLikeButton.vue';
import {
    edit as postsEdit,
    destroy as postsDestroy,
    index as postsIndex,
} from '@/routes/posts';
import type { Post } from '@/types';

interface Props {
    post: Post;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(postsDestroy.url(props.post.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">Post</h1>
                <div class="space-x-2">
                    <Link
                        :href="postsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="postsEdit.url(props.post.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
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
                <PostLikeButton :post="post" />
                <PostBasicDetails :post="post" />
                <PostCategoriesDetails :post="post" />
                <PostAuditDetails :post="post" />
                <PostComments :post="post" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete post"
            description="This post will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
