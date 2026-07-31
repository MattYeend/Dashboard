<script setup lang="ts">
import { Newspaper } from 'lucide-vue-next';
import type { DashboardLatestPost } from '@/types';

defineProps<{
    posts: DashboardLatestPost[];
}>();

const formatDate = (value: string): string =>
    new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
    >
        <div class="mb-3 flex items-center gap-2">
            <Newspaper class="size-4 text-gray-400" />
            <span class="text-sm font-medium text-gray-400">Latest posts</span>
        </div>

        <ul v-if="posts.length" class="divide-y divide-gray-500">
            <li
                v-for="post in posts"
                :key="post.id"
                class="flex items-center justify-between gap-4 py-2"
            >
                <span class="truncate text-sm text-gray-300">{{
                    post.title
                }}</span>
                <span class="shrink-0 text-xs text-gray-400">
                    {{ formatDate(post.created_at)
                    }}<template v-if="post.creator">
                        · {{ post.creator.name }}</template
                    >
                </span>
            </li>
        </ul>
        <p v-else class="text-sm text-gray-400">No posts yet.</p>
    </div>
</template>
