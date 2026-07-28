<script setup lang="ts">
import type { Post } from '@/types';

interface Props {
    post: Post;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Categories</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Assigned categories</dt>
                <dd class="text-sm">
                    <span v-if="!post.categories?.length">-</span>
                    <ul v-else class="space-y-1">
                        <li
                            v-for="category in post.categories"
                            :key="category.id"
                        >
                            {{ category.name }}
                        </li>
                    </ul>
                </dd>
            </div>
        </dl>
    </div>
</template>
