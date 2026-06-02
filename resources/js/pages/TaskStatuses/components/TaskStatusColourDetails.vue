<script setup lang="ts">
import type { TaskStatus } from '@/types'
defineProps<{ taskStatus: TaskStatus }>()
</script>

<template>
  <div class="space-y-2">
    <h2 class="font-medium text-sm uppercase tracking-wide">Colours</h2>
    <dl class="grid grid-cols-2 gap-2 text-sm">
      <dt class="font-medium">Background</dt>
      <dd class="flex items-centre gap-2">
        <span
          class="inline-block w-4 h-4 rounded border"
          :style="{ backgroundColor: taskStatus.background_colour }"
        />
        {{ taskStatus.background_colour }}
      </dd>
      <dt class="font-medium">Text</dt>
      <dd class="flex items-centre gap-2">
        <span
          class="inline-block w-4 h-4 rounded border"
          :style="{ backgroundColor: taskStatus.text_colour }"
        />
        {{ taskStatus.text_colour }}
      </dd>
      <dt class="font-medium">Preview</dt>
      <dd>
        <span
          class="inline-block px-2 py-1 rounded text-xs font-medium"
          :style="{ backgroundColor: taskStatus.background_colour, color: taskStatus.text_colour }"
        >
          {{ taskStatus.title }}
        </span>
      </dd>
    </dl>
  </div>
</template>