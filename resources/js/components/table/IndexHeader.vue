<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    title: string;
    createHref?: string;
    createLabel?: string;
    canCreate?: boolean;
    exportHref?: string;
    exportLabel?: string;
    canExport?: boolean;
}

withDefaults(defineProps<Props>(), {
    canCreate: true,
    exportLabel: 'Export',
    canExport: true,
});
</script>

<template>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-300">{{ title }}</h1>
        <div class="flex items-center gap-2">
            <a
                v-if="exportHref && canExport"
                :href="exportHref"
                class="inline-flex items-center rounded-md border border-gray-500 px-4 py-2 text-sm font-medium text-gray-300 shadow-sm hover:text-white"
            >
                {{ exportLabel }}
            </a>
            <Link
                v-if="canCreate && createHref"
                :href="createHref"
                class="inline-flex items-center rounded-md border border-gray-500 px-4 py-2 text-sm font-medium text-gray-300 shadow-sm hover:text-white"
            >
                {{ createLabel }}
            </Link>
        </div>
    </div>
</template>
