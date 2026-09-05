<script setup lang="ts">
import type { Report } from '@/types';

defineProps<{ report: Report }>();
</script>

<template>
    <div>
        <h2 class="text-sm font-semibold text-gray-300">Basic Details</h2>
        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2">
            <dt class="text-xs text-gray-400">Title</dt>
            <dd class="text-sm text-gray-200">{{ report.title }}</dd>

            <dt class="text-xs text-gray-400">Description</dt>
            <dd class="text-sm text-gray-200">
                {{ report.description ?? '—' }}
            </dd>

            <dt class="text-xs text-gray-400">Covers</dt>
            <dd class="text-sm text-gray-200">
                {{ report.type_label ?? report.type }}
            </dd>

            <dt class="text-xs text-gray-400">Format</dt>
            <dd class="text-sm text-gray-200 uppercase">{{ report.format }}</dd>
        </dl>
    </div>
</template>
