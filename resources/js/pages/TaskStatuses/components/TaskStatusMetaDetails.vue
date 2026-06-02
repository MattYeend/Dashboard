<script setup lang="ts">
import type { TaskStatus } from '@/types';
defineProps<{ taskStatus: TaskStatus }>();
</script>

<template>
    <div v-if="taskStatus.meta" class="space-y-2">
        <h2 class="text-sm font-medium tracking-wide uppercase">Meta</h2>
        <pre class="overflow-auto rounded border p-3 text-xs">{{
            JSON.stringify(taskStatus.meta, null, 2)
        }}</pre>
    </div>
</template>
