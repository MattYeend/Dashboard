<script setup lang="ts">
import type { Post } from '@/types';

interface Props {
    post: Post;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-md border border-gray-500 p-4">
        <dl>
            <dt class="text-sm font-medium text-gray-400">Tags</dt>
            <dd class="mt-1 text-sm text-gray-300">
                <span v-if="!post.tags?.length">—</span>
                <span v-else class="flex flex-wrap gap-2">
                    <span
                        v-for="tag in post.tags"
                        :key="tag.id"
                        class="rounded-full border border-gray-500 px-2 py-0.5 text-xs text-gray-300"
                    >
                        {{ tag.name }}
                    </span>
                </span>
            </dd>
        </dl>
    </div>
</template>
