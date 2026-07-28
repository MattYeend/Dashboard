<script setup lang="ts">
import DOMPurify from 'dompurify';
import { computed } from 'vue';
import type { Post } from '@/types';

interface Props {
    post: Post;
}

const props = defineProps<Props>();

const sanitisedDescription = computed(() =>
    DOMPurify.sanitize(props.post.description),
);
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Post details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm">
                    {{ post.title }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Description</dt>
                <dd
                    class="prose prose-sm max-w-none text-sm"
                    v-html="sanitisedDescription"
                />
            </div>
            <div>
                <dt class="text-xs text-gray-400">Image</dt>
                <dd class="text-sm">
                    <img
                        v-if="post.image"
                        :src="post.image"
                        :alt="post.title"
                        class="h-24 w-24 rounded-md object-cover"
                    />
                    <span v-else>-</span>
                </dd>
            </div>
        </dl>
    </div>
</template>
