<script setup lang="ts">
import CommentsSection from '@/components/Comments/CommentsSection.vue';
import type { Ticket } from '@/types';

interface Props {
    ticket: Ticket;
}

defineProps<Props>();
</script>

<template>
    <CommentsSection
        commentable-type="ticket"
        :commentable-id="ticket.id"
        :comments="ticket.comments ?? []"
        :comments-count="ticket.comments_count"
    />
</template>
