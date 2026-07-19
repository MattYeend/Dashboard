<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import { like as postsLike, unlike as postsUnlike } from '@/routes/posts';
import type { Post } from '@/types';

interface Props {
    post: Post;
}

const props = defineProps<Props>();

function toggleLike(): void {
    if (props.post.liked_by_user) {
        router.delete(postsUnlike.url(props.post.id), { preserveScroll: true });
    } else {
        router.post(postsLike.url(props.post.id), {}, { preserveScroll: true });
    }
}
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center gap-1 text-sm font-medium"
        @click="toggleLike"
    >
        <Heart :class="post.liked_by_user ? 'fill-current' : ''" class="h-4 w-4" />
        <span>{{ post.likes_count ?? 0 }}</span>
    </button>
</template>
