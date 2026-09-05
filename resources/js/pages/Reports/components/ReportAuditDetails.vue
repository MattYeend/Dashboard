<script setup lang="ts">
import type { Report } from '@/types';

defineProps<{ report: Report }>();
</script>

<template>
    <div>
        <h2 class="text-sm font-semibold text-gray-300">Audit</h2>
        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2">
            <dt class="text-xs text-gray-400">Created by</dt>
            <dd class="text-sm text-gray-200">{{ report.creator?.name ?? 'System' }}</dd>

            <dt class="text-xs text-gray-400">Updated by</dt>
            <dd class="text-sm text-gray-200">{{ report.updater?.name ?? 'System' }}</dd>

            <dt class="text-xs text-gray-400">Deleted by</dt>
            <dd class="text-sm text-gray-200">{{ report.deleter?.name ?? 'System' }}</dd>

            <dt class="text-xs text-gray-400">Restored by</dt>
            <dd class="text-sm text-gray-200">{{ report.restorer?.name ?? 'System' }}</dd>
        </dl>
    </div>
</template>