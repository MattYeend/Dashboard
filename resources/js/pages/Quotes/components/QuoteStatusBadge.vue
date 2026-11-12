<script setup lang="ts">
import type { Quote } from '@/types';

defineProps<{
    status: Quote['status'];
}>();
</script>

<template>
    <span
        v-if="status"
        class="inline-block rounded-md border-2 px-2 py-0.5 text-xs font-medium"
        :style="{ color: status.text_colour ?? undefined, borderColor: status.background_colour ?? undefined }"
    >
        {{ status.title }}
    </span>
</template>