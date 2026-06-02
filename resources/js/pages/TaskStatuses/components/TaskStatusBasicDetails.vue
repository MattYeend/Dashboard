<script setup lang="ts">
import type { TaskStatus } from '@/types';
defineProps<{ taskStatus: TaskStatus }>();
</script>

<template>
    <div class="space-y-2">
        <h2 class="text-sm font-medium tracking-wide uppercase">Details</h2>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="font-medium">Title</dt>
            <dd>{{ taskStatus.title }}</dd>
            <dt class="font-medium">Description</dt>
            <dd>{{ taskStatus.description ?? '—' }}</dd>
        </dl>
    </div>
</template>
