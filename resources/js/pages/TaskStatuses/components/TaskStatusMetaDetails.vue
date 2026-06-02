<script setup lang="ts">
import type { TaskStatus } from '@/types'
defineProps<{ taskStatus: TaskStatus }>()
</script>

<template>
  <div v-if="taskStatus.meta" class="space-y-2">
    <h2 class="font-medium text-sm uppercase tracking-wide">Meta</h2>
    <pre class="text-xs border rounded p-3 overflow-auto">{{ JSON.stringify(taskStatus.meta, null, 2) }}</pre>
  </div>
</template>