<script setup lang="ts">
import type { TaskStatus } from '@/types'
defineProps<{ taskStatus: TaskStatus }>()
</script>

<template>
    <div class="space-y-2">
        <h2 class="font-medium text-sm uppercase tracking-wide">Audit</h2>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="font-medium">Created by</dt>
            <dd>{{ taskStatus.creator?.name ?? '—' }}</dd>
            <dt class="font-medium">Updated by</dt>
            <dd>{{ taskStatus.updater?.name ?? '—' }}</dd>
            <dt class="font-medium">Deleted by</dt>
            <dd>{{ taskStatus.deleter?.name ?? '—' }}</dd>
            <dt class="font-medium">Restored by</dt>
            <dd>{{ taskStatus.restorer?.name ?? '—' }}</dd>
            <dt class="font-medium">Restored at</dt>
            <dd>{{ taskStatus.restored_at ?? '—' }}</dd>
        </dl>
    </div>
</template>