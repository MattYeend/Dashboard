<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import type { Comment } from '@/types';

interface Props {
    comment: Comment;
}

const props = defineProps<Props>();

function toggleLike(): void {
    const url = `/comments/${props.comment.id}/like`;

    if (props.comment.liked_by_user) {
        router.delete(url, { preserveScroll: true });
    } else {
        router.post(url, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center gap-1 text-xs"
        @click="toggleLike"
    >
        <Heart
            :class="comment.liked_by_user ? 'fill-current' : ''"
            class="h-3.5 w-3.5"
        />
        <span>{{ comment.likes_count ?? 0 }}</span>
    </button>
</template>
